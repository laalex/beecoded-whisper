# Feature: HubSpot Complete Lead History with AI Analysis

## Overview
Pull complete engagement history from HubSpot for a lead and analyze it with AI (Anthropic Claude) to provide deep insights, pattern recognition, and actionable recommendations.

## Status: COMPLETED
**Created**: 2025-11-26
**Last Updated**: 2025-11-26
**Completed**: 2025-11-26

## Problem Statement
The current implementation only fetches the last 10 engagements from HubSpot and uses 15 recent internal interactions for AI analysis. This limits the AI's ability to:
- Identify long-term communication patterns
- Detect engagement trends over time
- Provide contextual recommendations based on full relationship history
- Recognize seasonal or cyclical patterns in interactions

## Solution
Enhance the HubSpot integration to:
1. Fetch complete engagement history (emails, calls, meetings, notes, tasks)
2. Store history efficiently with pagination support
3. Provide AI-powered analysis of the complete history with pattern detection

## Technical Design

### 1. HubSpot API Enhancements

#### New Methods in HubSpotApiClient
```php
/**
 * Fetch all engagements with pagination
 * Uses HubSpot Engagements API v1 for complete history
 */
public function fetchAllEngagements(Integration $integration, string $contactId, int $limit = 100): array

/**
 * Fetch detailed engagement content (email body, call notes, etc.)
 */
public function fetchEngagementDetails(Integration $integration, array $engagementIds): array
```

#### Engagement Types to Fetch
- `EMAIL` - All email communications
- `CALL` - Phone calls with notes
- `MEETING` - Scheduled meetings
- `NOTE` - Manual notes
- `TASK` - Tasks and to-dos

### 2. New Service: LeadHistoryService

Location: `app/Services/HubSpot/LeadHistoryService.php`

```php
class LeadHistoryService
{
    /**
     * Fetch and process complete lead history from HubSpot
     */
    public function fetchCompleteHistory(Lead $lead): array

    /**
     * Aggregate and summarize history for AI analysis
     */
    public function prepareHistoryForAnalysis(array $history): array
}
```

### 3. Enhanced AI Analysis: LeadHistoryAnalysisService

Location: `app/Services/AI/LeadHistoryAnalysisService.php`

```php
class LeadHistoryAnalysisService
{
    /**
     * Perform deep analysis on complete lead history
     */
    public function analyzeHistory(Lead $lead): AiAnalysis

    /**
     * Extract patterns from engagement timeline
     */
    private function extractPatterns(array $history): array

    /**
     * Build comprehensive prompt with history context
     */
    private function buildHistoryPrompt(Lead $lead, array $historyContext): string
}
```

### 4. AI Analysis Output Structure

The AI will analyze the complete history and provide:

```json
{
  "history_summary": {
    "total_engagements": 45,
    "time_span_days": 180,
    "engagement_types_breakdown": {
      "email": 25,
      "call": 10,
      "meeting": 5,
      "note": 5
    },
    "average_response_time_hours": 4.5
  },
  "communication_patterns": {
    "preferred_channel": "email",
    "most_active_day": "Tuesday",
    "most_active_time": "10:00-12:00",
    "response_pattern": "typically responds within 24 hours",
    "engagement_frequency": "2-3 times per week during active periods"
  },
  "relationship_timeline": {
    "phases": [
      {
        "period": "2024-06 to 2024-08",
        "phase": "initial_contact",
        "key_events": ["First email", "Discovery call"],
        "sentiment": "positive"
      }
    ]
  },
  "key_topics_discussed": [
    {"topic": "pricing", "frequency": 8, "sentiment": "neutral"},
    {"topic": "implementation", "frequency": 5, "sentiment": "positive"}
  ],
  "decision_makers_identified": [
    {"name": "John Smith", "role": "CTO", "engagement_level": "high"}
  ],
  "buying_signals": [
    {
      "signal": "Requested pricing details",
      "date": "2024-10-15",
      "strength": "strong"
    }
  ],
  "objections_raised": [
    {
      "objection": "Budget constraints",
      "date": "2024-09-20",
      "status": "addressed",
      "resolution": "Offered payment plan"
    }
  ],
  "next_best_actions": [
    {
      "action": "Schedule follow-up call to discuss Q1 budget",
      "priority": "high",
      "optimal_timing": "First week of January",
      "rationale": "Based on historical pattern, lead becomes active after year-end budget approval"
    }
  ],
  "deal_prediction": {
    "likelihood_to_close": 72,
    "estimated_close_timeframe": "4-6 weeks",
    "confidence": 85
  }
}
```

### 5. Database Changes

No new tables required. The analysis results will be stored in `ai_analyses` table with `analysis_type = 'history'`.

New fields may be added to `enrichment_data`:
- `hubspot_history_count` - Total engagement count
- `hubspot_history_last_fetched_at` - Cache timestamp for full history

### 6. API Endpoints

```
GET  /api/leads/{id}/hubspot-history           # Fetch complete HubSpot history
POST /api/leads/{id}/analyze-history           # Trigger AI analysis on history
GET  /api/leads/{id}/history-analysis          # Get latest history analysis
```

### 7. Frontend Components

New component: `LeadHistoryAnalysis.tsx`
- Timeline visualization of engagement history
- Pattern charts (day/time heatmap)
- Key topics word cloud
- Decision makers network
- Buying signals timeline
- Objection tracker

## Acceptance Criteria

- [x] Complete engagement history fetched from HubSpot (all types)
- [x] Pagination support for leads with 100+ engagements
- [x] AI analysis includes communication patterns
- [x] AI identifies buying signals and objections
- [x] AI provides deal prediction with confidence score
- [x] Frontend displays history analysis results
- [x] Tests for HubSpot history fetching
- [x] Tests for AI history analysis
- [x] API documentation updated

## Dependencies

- HubSpot OAuth integration (existing)
- Anthropic API key configured (existing)
- AnthropicClient service (existing)

## Performance Considerations

- Full history fetch may take 5-10 seconds for leads with many engagements
- Consider background job for initial sync
- Cache history for 1 hour to avoid repeated API calls
- Limit AI context to summary + key engagements (avoid token limits)

## Security

- All data processed server-side
- HubSpot tokens never exposed to frontend
- API responses filtered to remove sensitive data (full email bodies)
