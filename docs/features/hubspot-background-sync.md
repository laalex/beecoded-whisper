# Feature: HubSpot Background Sync

## Overview
Automatically sync new contacts from HubSpot CRM every 15 minutes, creating leads in the system and triggering configurable actions for new imports.

## Status
- **Created**: 2025-11-27
- **Completed**: 2025-11-27
- **Status**: Complete

## Requirements

### Functional
1. Background job runs every 15 minutes via Laravel scheduler
2. Fetches contacts modified since last sync from HubSpot
3. Creates new leads for contacts not yet in system
4. Updates existing leads if HubSpot data changed
5. Tracks sync state (last sync timestamp, cursor)
6. Triggers events when new leads are imported
7. Supports configurable actions on new lead import:
   - Auto-assign to user/team
   - Run initial AI analysis
   - Send notification
   - Add to sequence

### Non-Functional
1. Handles API rate limits gracefully
2. Logs sync progress and errors
3. Supports manual trigger via artisan command
4. Idempotent - safe to run multiple times

## Technical Design

### Database Changes
- Add `sync_cursors` table to track sync state per integration

### New Components

#### 1. SyncCursor Model
```php
// Tracks sync state for each integration
- integration_id (FK)
- cursor_type (contacts, deals, etc.)
- last_sync_at
- cursor_value (HubSpot's after cursor)
- records_synced (count)
```

#### 2. HubSpotContactImportService
```php
class HubSpotContactImportService
{
    public function importNewContacts(Integration $integration): ImportResult
    public function importContact(array $hubspotContact, int $userId): Lead
}
```

#### 3. SyncHubSpotContacts Job
```php
class SyncHubSpotContacts implements ShouldQueue
{
    public function handle(): void
    // Dispatched by scheduler every 15 minutes
}
```

#### 4. Events
```php
LeadImportedFromHubSpot::class  // Fired for each new lead
HubSpotSyncCompleted::class     // Fired after sync batch completes
```

### API Endpoints
None required - this is background processing only.

### HubSpot API Usage
- `GET /crm/v3/objects/contacts` with `lastmodifieddate` filter
- Pagination via `after` cursor
- Properties: firstname, lastname, email, phone, company, jobtitle, etc.

## Implementation Steps

1. Create migration for `sync_cursors` table
2. Create SyncCursor model
3. Create HubSpotContactImportService
4. Create SyncHubSpotContacts job
5. Create events and listeners
6. Register in scheduler (every 15 minutes)
7. Add artisan command for manual trigger

## Testing Strategy

1. Unit tests for HubSpotContactImportService
2. Feature test for full sync flow (mocked HubSpot API)
3. Test idempotency (same contact imported twice)
4. Test cursor persistence
5. Test event dispatching

## Acceptance Criteria

- [x] Job runs every 15 minutes automatically
- [x] New HubSpot contacts become leads in system
- [x] Existing leads are updated if HubSpot data changed
- [x] Sync cursor persists between runs
- [x] Events fire for new lead imports
- [x] Logs provide visibility into sync status
- [x] Manual artisan command available

## Files Created

- `app/Models/SyncCursor.php` - Tracks sync state per integration
- `app/Services/HubSpot/HubSpotContactImportService.php` - Contact import logic
- `app/Jobs/SyncHubSpotContacts.php` - Background job
- `app/Console/Commands/SyncHubSpotCommand.php` - Manual trigger
- `app/Events/LeadImportedFromHubSpot.php` - Event for new imports
- `app/Events/HubSpotSyncCompleted.php` - Event after sync batch
- `app/Listeners/ProcessNewHubSpotLead.php` - Auto-analyze new leads
- `database/migrations/2025_11_26_221620_create_sync_cursors_table.php`
- `tests/Feature/HubSpotBackgroundSyncTest.php` - 8 tests

## Usage

```bash
# Run sync manually (queued)
php artisan hubspot:sync

# Run sync synchronously
php artisan hubspot:sync --sync

# Sync specific integration
php artisan hubspot:sync --integration=1

# The job runs automatically every 15 minutes via scheduler
php artisan schedule:work
```
