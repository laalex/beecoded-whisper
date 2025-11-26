<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Reminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $user = Auth::user();

        $totalLeads = Lead::where('user_id', $user->id)->count();

        $newLeadsToday = Lead::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->count();

        $hotLeads = Lead::where('user_id', $user->id)
            ->where('score', '>=', 80)
            ->count();

        $avgResponseTime = $this->calculateAvgResponseTime($user->id);

        $conversionRate = $this->calculateConversionRate($user->id);

        $pipelineValue = Lead::where('user_id', $user->id)
            ->whereNotIn('status', ['lost', 'converted'])
            ->sum('value');

        $upcomingReminders = Reminder::where('user_id', $user->id)
            ->where('is_completed', false)
            ->where('due_at', '>=', now())
            ->orderBy('due_at')
            ->limit(5)
            ->get();

        return response()->json([
            'total_leads' => $totalLeads,
            'new_leads_today' => $newLeadsToday,
            'hot_leads' => $hotLeads,
            'avg_response_time' => $avgResponseTime,
            'conversion_rate' => round($conversionRate, 1),
            'pipeline_value' => $pipelineValue,
            'upcoming_reminders' => $upcomingReminders,
        ]);
    }

    public function responseTimes(): JsonResponse
    {
        $user = Auth::user();

        $leads = Lead::where('user_id', $user->id)
            ->whereHas('interactions')
            ->with(['interactions' => function ($query) {
                $query->orderBy('occurred_at', 'asc')->limit(1);
            }])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $responseTimes = $leads->map(function ($lead) {
            $firstInteraction = $lead->interactions->first();
            if (!$firstInteraction) {
                return null;
            }

            $responseMinutes = $lead->created_at->diffInMinutes($firstInteraction->occurred_at);
            return [
                'lead_id' => $lead->id,
                'lead_name' => $lead->first_name . ' ' . $lead->last_name,
                'response_time' => $responseMinutes,
                'created_at' => $lead->created_at,
            ];
        })->filter()->values();

        return response()->json($responseTimes);
    }

    private function calculateAvgResponseTime(int $userId): int
    {
        $leads = Lead::where('user_id', $userId)
            ->whereHas('interactions')
            ->with(['interactions' => function ($query) {
                $query->orderBy('occurred_at', 'asc')->limit(1);
            }])
            ->get();

        if ($leads->isEmpty()) {
            return 0;
        }

        $totalMinutes = 0;
        $count = 0;

        foreach ($leads as $lead) {
            $firstInteraction = $lead->interactions->first();
            if ($firstInteraction) {
                $totalMinutes += $lead->created_at->diffInMinutes($firstInteraction->occurred_at);
                $count++;
            }
        }

        return $count > 0 ? (int) round($totalMinutes / $count) : 0;
    }

    private function calculateConversionRate(int $userId): float
    {
        $totalLeads = Lead::where('user_id', $userId)->count();

        if ($totalLeads === 0) {
            return 0;
        }

        $convertedLeads = Lead::where('user_id', $userId)
            ->where('status', 'converted')
            ->count();

        return ($convertedLeads / $totalLeads) * 100;
    }
}
