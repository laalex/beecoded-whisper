# HubSpot Data Sync & AI Analysis

**Status**: Completed
**Created**: 2025-11-26
**Completed**: 2025-11-26
**Author**: Claude

## Overview

Automatically sync HubSpot contact data when viewing a lead (with 30-minute cache), and provide comprehensive AI-powered analysis using Anthropic Claude API including scoring insights, nurturing recommendations, and conversation analysis.

## Goals

1. Auto-refresh HubSpot data when stale (older than 30 minutes)
2. Display comprehensive HubSpot contact properties on lead detail page
3. Provide AI-powered lead analysis using Anthropic Claude
4. Show intelligent nurturing recommendations
5. Analyze interaction history for sentiment and patterns

## User Stories

### US-1: Auto-Sync HubSpot Data
As a sales rep, when I view a lead's detail page, I want HubSpot data to be automatically refreshed if stale so I always see current information.

**Acceptance Criteria:**
- If lead has `external_id` (HubSpot ID) and enrichment data is >30 mins old, auto-refresh
- Display sync status indicator (syncing, synced X mins ago, error)
- Allow manual refresh button
- Show extended HubSpot properties (lifecycle stage, deal associations, etc.)

### US-2: AI Lead Analysis
As a sales rep, I want AI-powered insights about my lead so I can make informed decisions.

**Acceptance Criteria:**
- Display AI analysis card with key insights
- Show engagement patterns and optimal contact times
- Provide deal probability assessment
- Highlight risks and opportunities

### US-3: Smart Nurturing Recommendations
As a sales rep, I want personalized action recommendations so I know what to do next.

**Acceptance Criteria:**
- Show prioritized next actions
- Provide talking points for next conversation
- Suggest relevant content to share
- Allow one-click reminder creation from recommendations

### US-4: Interaction Analysis
As a sales rep, I want AI analysis of my interaction history to understand relationship health.

**Acceptance Criteria:**
- Sentiment trend over time
- Communication pattern analysis
- Key topics and concerns identified
- Relationship strength indicator

## Technical Requirements

### API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/leads/{id}` | Returns lead with fresh HubSpot data and AI analysis |
| POST | `/api/leads/{id}/sync` | Force sync HubSpot data |
| GET | `/api/leads/{id}/analysis` | Get AI analysis (cached 15 mins) |
| POST | `/api/leads/{id}/analyze` | Force new AI analysis |

### Database Changes

**Add to `enrichment_data` table:**
- `hubspot_lifecycle_stage` - string
- `hubspot_deals` - json
- `hubspot_activities` - json
- `hubspot_owner` - json
- `last_synced_at` - timestamp
- `sync_error` - string nullable

**Add new `ai_analysis` table:**
- `lead_id` - foreign key
- `analysis_type` - enum (full, scoring, nurturing, sentiment)
- `insights` - json
- `recommendations` - json
- `risks` - json
- `opportunities` - json
- `confidence_score` - decimal
- `model_used` - string
- `analyzed_at` - timestamp

### Services Architecture

```
App\Services\
├── HubSpot/
│   └── HubSpotSyncService.php    # Fetch extended contact data
├── AI/
│   ├── LeadAnalysisService.php   # Comprehensive AI analysis
│   ├── NurturingService.php      # Existing, enhanced
│   └── AnthropicClient.php       # Reusable API client
└── Scoring/
    └── LeadScoringService.php    # Existing, integrate with AI
```

### Data Flow

```
Lead Detail Page Request
         │
         ▼
┌─────────────────────────────┐
│ LeadController::show()      │
│ - Check enrichment staleness│
│ - Dispatch sync if needed   │
│ - Return lead + analysis    │
└─────────────────────────────┘
         │
    ┌────┴────┐
    ▼         ▼
┌────────┐ ┌────────────┐
│HubSpot │ │ AI Analysis│
│ Sync   │ │  Service   │
└────────┘ └────────────┘
    │            │
    ▼            ▼
┌────────────────────────────┐
│    enrichment_data         │
│    ai_analysis             │
└────────────────────────────┘
```

## Frontend Components

```
src/frontend/src/components/leads/
├── LeadHubSpotData.tsx       # HubSpot properties card
├── LeadAIAnalysis.tsx        # AI insights card
├── LeadRecommendations.tsx   # Nurturing actions card
└── SyncStatusBadge.tsx       # Sync indicator
```

## AI Prompt Structure

```json
{
  "analysis_request": {
    "lead_profile": {...},
    "interaction_history": [...],
    "current_status": "...",
    "deal_context": {...}
  },
  "requested_analysis": [
    "engagement_patterns",
    "sentiment_analysis",
    "deal_probability",
    "risk_assessment",
    "next_actions",
    "talking_points"
  ]
}
```

## Caching Strategy

| Data | Cache Duration | Invalidation |
|------|---------------|--------------|
| HubSpot contact data | 30 minutes | Manual sync, webhook |
| AI analysis | 15 minutes | New interaction, status change |
| Nurturing recommendations | 15 minutes | New interaction |

## Test Plan

### Backend Tests
- [x] HubSpotSyncService fetches extended properties
- [x] Auto-sync triggers when data is stale
- [x] AI analysis returns structured insights
- [x] Cache invalidation works correctly
- [x] Error handling for API failures

### Frontend Tests
- [x] HubSpot data displays correctly
- [x] AI analysis card renders insights
- [x] Sync status updates properly
- [x] Loading states show during sync

## Error Handling

- HubSpot API failure: Show last known data with error indicator
- Anthropic API failure: Fall back to rule-based recommendations
- Rate limiting: Queue sync requests, show queued status

## Security Considerations

- Store HubSpot tokens encrypted
- Never expose API keys to frontend
- Rate limit sync requests per user
- Validate HubSpot webhook signatures
