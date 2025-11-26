# Project: Bee Coded Whisper

## Overview
AI-powered Sales Intelligence Platform for lead management, scoring, nurturing, and automated sales workflows with HubSpot and Gmail integrations.

## Tech Stack
- **Backend**: Laravel 12+ (PHP 8.3+)
- **Frontend**: React 19+ with TypeScript 5.6+
- **Styling**: Tailwind CSS 4+
- **Build Tool**: Vite 6+
- **Database**: PostgreSQL 17+ (or MySQL 8.4+)
- **Cache**: Redis 7.4+
- **Queue**: Laravel Queues with Redis
- **Containers**: Docker + Docker Compose V2
- **Runtime**: Node.js 22 LTS

---

## 🤖 AUTONOMOUS WORKFLOW ENGINE

### Operating Mode: AUTONOMOUS

When receiving any task or business request, Claude MUST follow this decision engine automatically without waiting for explicit commands.

### Decision Engine

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    AUTONOMOUS WORKFLOW ENGINE                           │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  INPUT: Business Request / Feature / Bug / Task                         │
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │ PHASE 1: ANALYSIS                                                │   │
│  │ • Parse request to understand scope                              │   │
│  │ • Check if feature spec exists in docs/features/                 │   │
│  │ • If NO spec exists → Create one first                           │   │
│  │ • Identify affected components (backend/frontend/both)           │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                              ↓                                          │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │ PHASE 2: ARCHITECTURE CHECK                                      │   │
│  │ • Does this need new API endpoints? → Design them                │   │
│  │ • Does this need DB changes? → Plan migrations                   │   │
│  │ • Is this a significant decision? → Create ADR                   │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                              ↓                                          │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │ PHASE 3: TDD IMPLEMENTATION                                      │   │
│  │ • ALWAYS write tests FIRST                                       │   │
│  │ • Run tests (expect failure)                                     │   │
│  │ • Implement minimum code to pass                                 │   │
│  │ • Run tests (expect success)                                     │   │
│  │ • Refactor if needed (tests must stay green)                     │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                              ↓                                          │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │ PHASE 4: QUALITY GATES                                           │   │
│  │ • Check file sizes (max 250-300 lines)                           │   │
│  │ • If too large → Split immediately                               │   │
│  │ • Run linting                                                    │   │
│  │ • Verify all tests pass                                          │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                              ↓                                          │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │ PHASE 5: DOCUMENTATION                                           │   │
│  │ • Update/close feature spec                                      │   │
│  │ • Update API docs if endpoints changed                           │   │
│  │ • Update CLAUDE.md task list                                     │   │
│  │ • Add changelog entry                                            │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### Automatic Skill Loading

Before ANY implementation work, Claude MUST read the relevant skills:

| Task Type | Required Skills to Read |
|-----------|------------------------|
| New feature | `project-planning` → `architecture` → `testing-tdd` → `backend-laravel` and/or `frontend-react` → `documentation` |
| Bug fix | `testing-tdd` → relevant implementation skill → `documentation` |
| Refactoring | `refactoring` → `testing-tdd` |
| API work | `architecture` → `backend-laravel` → `testing-tdd` |
| UI work | `ui-ux-guidelines` → `frontend-react` → `testing-tdd` |
| DevOps | `environment-setup` or `deployment` |

### Mandatory Behaviors

These rules are ALWAYS enforced, regardless of request:

1. **TDD is non-negotiable**
   - Never write implementation code without tests first
   - If tests don't exist for modified code, write them first

2. **File size limits are enforced**
   - Before saving any file, check line count
   - If > 250 lines, split before proceeding

3. **Documentation is automatic**
   - Every feature completion updates docs
   - Every API change updates API docs
   - CLAUDE.md is updated after every task

4. **Quality checks run automatically**
   - After implementation: run tests
   - After refactoring: run tests
   - Before marking complete: verify all tests pass

### Request Classification

Claude automatically classifies incoming requests:

| Keywords/Patterns | Classification | Workflow |
|-------------------|----------------|----------|
| "add", "create", "implement", "build", "new feature" | NEW_FEATURE | Full workflow |
| "fix", "bug", "broken", "error", "issue" | BUG_FIX | Test → Fix → Test → Doc |
| "refactor", "clean up", "improve", "optimize" | REFACTOR | Test → Refactor → Test |
| "update", "change", "modify" | MODIFICATION | Spec → Test → Implement → Doc |
| "setup", "configure", "install" | INFRASTRUCTURE | Environment skill |
| "deploy", "release", "publish" | DEPLOYMENT | Deployment skill |
| "document", "explain", "describe" | DOCUMENTATION | Documentation skill |

### Execution Protocol

When Claude receives a business request:

```
1. ACKNOWLEDGE
   "I'll implement [summary]. This will involve: [list phases]"

2. READ SKILLS
   Silently load all required skills for the task type

3. PLAN
   Brief outline of what will be created/modified

4. EXECUTE
   Follow the phases automatically, showing progress:
   - "📋 Creating feature spec..."
   - "🏗️ Designing architecture..."
   - "🧪 Writing tests..."
   - "💻 Implementing..."
   - "✅ Running tests..."
   - "📝 Updating documentation..."

5. REPORT
   Summary of what was done, files created/modified, tests status
```

### Self-Correction Rules

If at any point:
- Tests fail → Fix before proceeding (never skip)
- File too large → Split before proceeding
- Missing spec → Create before implementing
- Missing tests → Write before implementing
- Unclear requirements → Ask ONE clarifying question, then proceed with best judgment

---

## Project Structure

```
├── .claude/
│   └── skills/             # Claude Code skills (auto-discovered)
├── docs/                    # Documentation
│   ├── features/           # Feature specifications
│   ├── architecture/       # Architecture decisions
│   └── decisions/          # ADRs
├── docker/                  # Docker configurations
│   ├── development/
│   ├── testing/
│   └── production/
├── src/
│   ├── backend/            # Laravel application
│   └── frontend/           # React application
└── tests/
    ├── unit/
    ├── integration/
    └── e2e/
```

## Skills Reference

Read these before any task:
- `.claude/skills/project-planning/SKILL.md` - Feature research and specs
- `.claude/skills/architecture/SKILL.md` - System design
- `.claude/skills/environment-setup/SKILL.md` - Docker configuration
- `.claude/skills/backend-laravel/SKILL.md` - Laravel development
- `.claude/skills/frontend-react/SKILL.md` - React development
- `.claude/skills/ui-ux-guidelines/SKILL.md` - Design system
- `.claude/skills/testing-tdd/SKILL.md` - TDD workflow
- `.claude/skills/documentation/SKILL.md` - Documentation standards
- `.claude/skills/deployment/SKILL.md` - CI/CD pipeline
- `.claude/skills/refactoring/SKILL.md` - Code maintenance

---

## Current Sprint

### Completed
- [x] Project setup and documentation
- [x] Docker development environment
- [x] Laravel backend with API routes
- [x] Database migrations (leads, interactions, sequences, offers, etc.)
- [x] RBAC system with Spatie Permission
- [x] HubSpot OAuth integration
- [x] Gmail OAuth integration
- [x] Lead scoring engine
- [x] AI transcription service (ElevenLabs)
- [x] Nurturing recommendations system (Claude AI)
- [x] React + Vite frontend with Tailwind
- [x] Authentication (login/register)
- [x] Dashboard page
- [x] Leads management page
- [x] Feature tests for Auth and Leads
- [x] Lead detail page (with interactions, reminders, offers, score breakdown)
- [x] HubSpot/Gmail OAuth frontend integration
- [x] HubSpot data auto-sync with 30-minute cache
- [x] AI-powered lead analysis using Anthropic Claude API
- [x] Complete HubSpot engagement history pull
- [x] AI-powered history analysis with pattern detection, buying signals, deal prediction

### In Progress
- [ ] Voice input functionality
- [ ] Offer generation

### Backlog
- [ ] Preply enrichment integration
- [ ] Advanced analytics dashboard
- [ ] Email sequence builder UI
- [ ] Mobile responsive improvements
- [ ] Real-time notifications (WebSockets)

---

## Quality Metrics

Track these automatically:
- [ ] Test coverage > 80%
- [ ] No files > 300 lines
- [ ] All features have specs
- [ ] All APIs documented
- [ ] Zero failing tests

---
*Last updated: 2025-11-26*
*Autonomous mode: ENABLED*
