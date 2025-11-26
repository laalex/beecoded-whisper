# Lead Detail Page

**Status**: Completed
**Created**: 2025-11-26
**Author**: Claude

## Overview

A comprehensive lead detail page that displays all information about a single lead, including contact details, interactions history, score breakdown, reminders, offers, and quick actions.

## Goals

1. Provide a complete view of a lead's information and history
2. Enable quick actions (update status, add interaction, create reminder)
3. Display AI-generated nurturing recommendations
4. Show score breakdown with contributing factors
5. Allow inline editing of lead properties

## User Stories

### US-1: View Lead Details
As a sales rep, I want to view all details of a lead so I can understand their profile and history.

**Acceptance Criteria:**
- Display lead contact information (name, email, phone, company, job title)
- Show lead metadata (source, created date, last contacted, next followup)
- Display current status and score with breakdown
- Show assigned user information

### US-2: View Interaction History
As a sales rep, I want to see all interactions with a lead chronologically so I can understand our relationship.

**Acceptance Criteria:**
- List all interactions in reverse chronological order
- Show interaction type icon, date, subject, and summary
- Display sentiment indicator if available
- Allow expanding to see full content

### US-3: Update Lead Status
As a sales rep, I want to quickly change a lead's status so I can track pipeline progression.

**Acceptance Criteria:**
- Status selector with all valid statuses
- Immediate update with optimistic UI
- Success/error feedback

### US-4: Add Interaction
As a sales rep, I want to log a new interaction so I can track my communication history.

**Acceptance Criteria:**
- Quick add form for common interaction types (call, email, meeting, note)
- Required fields: type, content/summary
- Optional: subject, direction, sentiment
- Auto-set occurred_at to now

### US-5: View and Manage Reminders
As a sales rep, I want to see and manage reminders for this lead so I don't miss follow-ups.

**Acceptance Criteria:**
- List upcoming and overdue reminders
- Quick complete action
- Link to create new reminder

### US-6: View Offers
As a sales rep, I want to see all offers sent to this lead so I can track negotiations.

**Acceptance Criteria:**
- List all offers with status, amount, and dates
- Quick link to create new offer

### US-7: Edit Lead Information
As a sales rep, I want to edit lead details inline so I can keep information current.

**Acceptance Criteria:**
- Edit contact fields
- Update notes
- Change assigned user
- Set next follow-up date

## Technical Requirements

### API Endpoints

The backend already has the required endpoints:

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/leads/{id}` | Get lead with relationships |
| PUT | `/api/leads/{id}` | Update lead |
| GET | `/api/leads/{lead}/history` | Get interaction history |
| POST | `/api/interactions` | Create interaction |
| GET | `/api/nurturing/recommendations/{lead}` | Get AI recommendations |

### Frontend Components

```
src/frontend/src/
├── pages/
│   └── LeadDetail.tsx          # Main page container
├── components/
│   └── leads/
│       ├── LeadHeader.tsx      # Name, status, score, actions
│       ├── LeadContactInfo.tsx # Contact details card
│       ├── LeadTimeline.tsx    # Interactions timeline
│       ├── LeadReminders.tsx   # Reminders section
│       ├── LeadOffers.tsx      # Offers section
│       ├── LeadScoreCard.tsx   # Score breakdown
│       └── AddInteractionModal.tsx # Quick add interaction
├── hooks/
│   └── useLead.ts              # Lead data fetching hook
└── services/
    └── leads.ts                # API calls
```

### Data Flow

1. Page loads with lead ID from URL params
2. Fetch lead details with all relationships
3. Display sections using dedicated components
4. Mutations use React Query for cache updates
5. Optimistic updates for status changes

## UI/UX Requirements

### Layout

```
┌──────────────────────────────────────────────────────────────────┐
│ ← Back to Leads    [Edit] [Delete]                               │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────────────────────┐  ┌───────────────────────────┐  │
│  │ Avatar + Name               │  │ Score Card                │  │
│  │ Company · Title             │  │ 85/100                    │  │
│  │ Status: [Dropdown]          │  │ ────────────────          │  │
│  │                             │  │ Engagement: 90            │  │
│  │ Quick Actions:              │  │ Fit: 80                   │  │
│  │ [📞 Call] [✉️ Email]        │  │ Behavior: 85              │  │
│  │ [📝 Note] [⏰ Reminder]     │  │                           │  │
│  └─────────────────────────────┘  └───────────────────────────┘  │
│                                                                  │
│  ┌─────────────────────────────┐  ┌───────────────────────────┐  │
│  │ Contact Information         │  │ Reminders                 │  │
│  │ ────────────────────────    │  │ ──────────────────        │  │
│  │ Email: john@example.com     │  │ ⏰ Follow up call (Today) │  │
│  │ Phone: +1 555-123-4567      │  │ ☐ Send proposal (Tomorrow)│  │
│  │ Website: example.com        │  │                           │  │
│  │ LinkedIn: /in/johndoe       │  │ [+ Add Reminder]          │  │
│  │                             │  └───────────────────────────┘  │
│  │ Next Followup: Dec 1, 2025  │                                 │
│  │ Value: $50,000              │  ┌───────────────────────────┐  │
│  └─────────────────────────────┘  │ Offers                    │  │
│                                   │ ──────────────────        │  │
│  ┌─────────────────────────────┐  │ Proposal #1 - $5,000 SENT │  │
│  │ Timeline / History          │  │ Proposal #2 - $8,000 DRAFT│  │
│  │ ────────────────────────    │  │                           │  │
│  │ ● Call - Nov 25             │  │ [+ Create Offer]          │  │
│  │   Discussed pricing...      │  └───────────────────────────┘  │
│  │                             │                                 │
│  │ ● Email - Nov 20            │                                 │
│  │   Sent follow-up...         │                                 │
│  │                             │                                 │
│  │ ● Meeting - Nov 15          │                                 │
│  │   Initial demo...           │                                 │
│  │                             │                                 │
│  │ [Load More]                 │                                 │
│  └─────────────────────────────┘                                 │
│                                                                  │
│  ┌───────────────────────────────────────────────────────────┐   │
│  │ Notes                                                     │   │
│  │ ─────────────────────────────────────────────────────     │   │
│  │ [Editable textarea with save button]                      │   │
│  └───────────────────────────────────────────────────────────┘   │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

### Styling

- Use existing Card, Badge, Button components
- Navy (#1a1f36) for headers and primary elements
- Yellow (#f7e547) for accents and CTAs
- Clean, Apple-inspired aesthetic with proper spacing
- Responsive: stack columns on mobile

## Test Plan

### Backend Tests
- [x] GET /leads/{id} returns lead with all relationships
- [x] Unauthorized users cannot access other users' leads
- [x] History endpoint returns interactions in correct order

### Frontend Tests
- [x] LeadDetail page renders with mock data
- [x] Status change updates UI optimistically
- [x] Add interaction modal submits correctly
- [x] Loading and error states display properly

## Implementation Notes

- The backend `show` method already loads all needed relationships
- Use React Query's `useQuery` with stale-while-revalidate
- Implement optimistic updates for better UX
- Split page into small, focused components (< 150 lines each)
