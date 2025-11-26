# Feature: HubSpot Integration Frontend

**Status**: Complete
**Author**: Claude
**Created**: 2025-11-26
**Updated**: 2025-11-26

## Summary
Implement the frontend UI for connecting, managing, and syncing HubSpot CRM integration, allowing users to import contacts as leads.

## Problem Statement
The backend HubSpot OAuth and sync functionality exists but there's no frontend interface for users to connect their HubSpot account, view connection status, trigger syncs, or disconnect.

## Goals
- [x] Display list of connected integrations
- [x] Allow users to connect HubSpot via OAuth
- [x] Show connection status and last sync time
- [x] Allow manual sync trigger
- [x] Allow disconnection of HubSpot

## Non-Goals
- Custom field mapping (use defaults)
- Automated sync scheduling (manual only for now)
- HubSpot deals/companies sync (contacts only)

## User Stories

### Story 1: Connect HubSpot
**As a** sales representative
**I want** to connect my HubSpot account
**So that** I can import my contacts as leads

### Story 2: View Integration Status
**As a** user
**I want** to see my connected integrations and their status
**So that** I know if my data is syncing properly

### Story 3: Sync Contacts
**As a** user
**I want** to manually trigger a sync
**So that** I can import the latest contacts from HubSpot

### Story 4: Disconnect HubSpot
**As a** user
**I want** to disconnect my HubSpot account
**So that** I can revoke access if needed

## Acceptance Criteria

### Criterion 1: Integration List Display
**Given** a user is on the Integrations page
**When** the page loads
**Then** they see a list of available integrations (HubSpot, Gmail) with connection status

### Criterion 2: Connect HubSpot
**Given** HubSpot is not connected
**When** user clicks "Connect HubSpot"
**Then** they are redirected to HubSpot OAuth page
**And** after approval, redirected back with connection confirmed

### Criterion 3: Sync Contacts
**Given** HubSpot is connected
**When** user clicks "Sync Now"
**Then** sync is triggered and status updates to show syncing
**And** last_synced_at is updated on completion

### Criterion 4: Disconnect Integration
**Given** HubSpot is connected
**When** user clicks "Disconnect"
**Then** integration is removed and status shows disconnected

## Technical Considerations

### API Endpoints (Already Exist)
- `GET /api/integrations` - List user's integrations
- `POST /api/integrations/hubspot/connect` - Get OAuth URL
- `GET /api/integrations/hubspot/callback` - OAuth callback
- `POST /api/integrations/{id}/sync` - Trigger sync
- `DELETE /api/integrations/{id}` - Disconnect

### Frontend Components Needed
- `IntegrationsPage` - Main page component
- `IntegrationCard` - Card for each integration type
- `useIntegrations` - React Query hook for fetching integrations
- `useConnectHubSpot` - Mutation hook for OAuth flow
- `useSyncIntegration` - Mutation hook for syncing
- `useDisconnectIntegration` - Mutation hook for disconnecting

### OAuth Flow
1. User clicks "Connect HubSpot"
2. Frontend calls `POST /api/integrations/hubspot/connect`
3. Backend returns OAuth URL
4. Frontend opens OAuth URL (popup or redirect)
5. User approves on HubSpot
6. HubSpot redirects to callback URL
7. Backend processes callback, stores tokens
8. Frontend detects completion and refreshes integration list

## Tasks

| Task | Estimate | Priority | Dependencies |
|------|----------|----------|--------------|
| Create TypeScript types for Integration | 30m | High | None |
| Create API client functions | 30m | High | Types |
| Create React Query hooks | 1h | High | API client |
| Create IntegrationCard component | 1h | High | Hooks |
| Create IntegrationsPage | 1h | High | IntegrationCard |
| Handle OAuth popup/redirect flow | 1h | High | IntegrationsPage |
| Add loading/error states | 30m | Medium | All above |
| Write component tests | 1h | Medium | Components |

## Open Questions
- [x] Use popup or redirect for OAuth? → Popup with fallback to redirect

## References
- Backend HubSpotService: `src/backend/app/Services/HubSpot/HubSpotService.php`
- Integration Controller: `src/backend/app/Http/Controllers/Api/IntegrationController.php`
- API Routes: `src/backend/routes/api.php`
