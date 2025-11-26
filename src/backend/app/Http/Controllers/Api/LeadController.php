<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiAnalysis;
use App\Models\Lead;
use App\Services\AI\LeadAnalysisService;
use App\Services\AI\LeadHistoryAnalysisService;
use App\Services\HubSpot\HubSpotSyncService;
use App\Services\HubSpot\LeadHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct(
        private HubSpotSyncService $hubSpotSync,
        private LeadAnalysisService $analysisService,
        private LeadHistoryService $historyService,
        private LeadHistoryAnalysisService $historyAnalysisService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Lead::with(['assignee', 'scoreDetails']);

        if ($request->user()->can('leads.view_all')) {
            // Managers and admins can see all leads
        } else {
            $query->where(function ($q) use ($request) {
                $q->where('user_id', $request->user()->id)
                    ->orWhere('assigned_to', $request->user()->id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if ($request->filled('min_score')) {
            $query->where('score', '>=', $request->min_score);
        }

        if ($request->filled('is_vip')) {
            $query->where('is_vip', filter_var($request->is_vip, FILTER_VALIDATE_BOOLEAN));
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDir);

        $leads = $query->paginate($request->get('per_page', 15));

        return response()->json($leads);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'status' => 'nullable|in:new,contacted,qualified,proposal,negotiation,won,lost,dormant',
            'estimated_value' => 'nullable|numeric|min:0',
            'tags' => 'nullable|array',
            'custom_fields' => 'nullable|array',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'source' => 'nullable|string|max:50',
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['source'] = $validated['source'] ?? 'manual';

        $lead = Lead::create($validated);

        return response()->json($lead->load('assignee'), 201);
    }

    public function show(Lead $lead): JsonResponse
    {
        $this->authorizeLeadAccess($lead);

        // Auto-sync HubSpot data if stale (30 minutes)
        if ($lead->source === 'hubspot' && $lead->external_id) {
            $this->hubSpotSync->syncLeadIfStale($lead);
            $lead->refresh();
        }

        // AI analysis is persisted in DB - only auto-generate if none exists
        // Users can manually re-analyze via the analyze endpoint
        if (!$lead->aiAnalysis) {
            $this->analysisService->analyzeLead($lead);
            $lead->refresh();
        }

        return response()->json(
            $lead->load([
                'assignee',
                'interactions.user',
                'offers',
                'scoreDetails',
                'enrichmentData',
                'reminders',
                'aiAnalysis'
            ])
        );
    }

    public function update(Request $request, Lead $lead): JsonResponse
    {
        $this->authorizeLeadAccess($lead);

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'status' => 'nullable|in:new,contacted,qualified,proposal,negotiation,won,lost,dormant',
            'is_vip' => 'nullable|boolean',
            'estimated_value' => 'nullable|numeric|min:0',
            'tags' => 'nullable|array',
            'custom_fields' => 'nullable|array',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'next_followup_at' => 'nullable|date',
        ]);

        $lead->update($validated);

        return response()->json($lead->fresh('assignee'));
    }

    public function destroy(Lead $lead): JsonResponse
    {
        $this->authorizeLeadAccess($lead);

        $lead->delete();

        return response()->json(['message' => 'Lead deleted successfully']);
    }

    public function history(Lead $lead): JsonResponse
    {
        $this->authorizeLeadAccess($lead);

        $interactions = $lead->interactions()
            ->with('user')
            ->orderBy('occurred_at', 'desc')
            ->get();

        return response()->json($interactions);
    }

    public function similar(Lead $lead): JsonResponse
    {
        $this->authorizeLeadAccess($lead);

        $similar = Lead::where('id', '!=', $lead->id)
            ->where(function ($query) use ($lead) {
                if ($lead->company) {
                    $query->orWhere('company', 'like', "%{$lead->company}%");
                }
                if ($lead->job_title) {
                    $query->orWhere('job_title', 'like', "%{$lead->job_title}%");
                }
            })
            ->limit(10)
            ->get();

        return response()->json($similar);
    }

    public function sync(Lead $lead): JsonResponse
    {
        $this->authorizeLeadAccess($lead);

        if ($lead->source !== 'hubspot' || !$lead->external_id) {
            return response()->json([
                'message' => 'Lead is not synced with HubSpot'
            ], 400);
        }

        $enrichment = $this->hubSpotSync->syncLead($lead);

        return response()->json([
            'message' => 'Sync completed',
            'enrichment_data' => $enrichment,
            'sync_error' => $enrichment?->sync_error,
        ]);
    }

    public function analyze(Lead $lead): JsonResponse
    {
        $this->authorizeLeadAccess($lead);

        $analysis = $this->analysisService->analyzeLead($lead);

        return response()->json([
            'message' => 'Analysis completed',
            'analysis' => $analysis,
        ]);
    }

    /**
     * Fetch complete HubSpot engagement history for a lead
     */
    public function hubspotHistory(Request $request, Lead $lead): JsonResponse
    {
        $this->authorizeLeadAccess($lead);

        if (!$lead->external_id) {
            return response()->json([
                'message' => 'Lead is not linked to HubSpot'
            ], 400);
        }

        try {
            $limit = $request->get('limit', 100);
            $history = $this->historyService->fetchCompleteHistory($lead, $limit);

            return response()->json($history);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Perform AI analysis on lead's HubSpot history
     */
    public function analyzeHistory(Lead $lead): JsonResponse
    {
        $this->authorizeLeadAccess($lead);

        $analysis = $this->historyAnalysisService->analyzeHistory($lead);

        return response()->json($analysis);
    }

    /**
     * Get the latest history analysis for a lead
     */
    public function getHistoryAnalysis(Lead $lead): JsonResponse
    {
        $this->authorizeLeadAccess($lead);

        $analysis = AiAnalysis::where('lead_id', $lead->id)
            ->where('analysis_type', 'history')
            ->latest('analyzed_at')
            ->first();

        if (!$analysis) {
            return response()->json([
                'message' => 'No history analysis found'
            ], 404);
        }

        return response()->json($analysis);
    }

    private function authorizeLeadAccess(Lead $lead): void
    {
        $user = request()->user();

        if ($user->can('leads.view_all')) {
            return;
        }

        if ($lead->user_id !== $user->id && $lead->assigned_to !== $user->id) {
            abort(403, 'Unauthorized access to this lead');
        }
    }
}
