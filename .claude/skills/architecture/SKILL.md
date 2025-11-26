---
name: architecture
description: System architecture design, API design, database schema planning, and architecture decision records (ADRs). Use when designing new features, planning system components, defining API contracts, creating database schemas, or documenting architectural decisions.
---

# Architecture Skill

## Workflow

1. **Review Requirements** → Read feature spec from `docs/features/`
2. **Design Components** → Define services, modules, interactions
3. **Define Contracts** → API specifications, data models
4. **Document Decision** → Create ADR for significant choices
5. **Plan Schema** → Database design and migrations

## Architecture Patterns

### Backend (Laravel)
```
┌─────────────────────────────────────────────┐
│                  Controllers                 │
│            (HTTP layer, validation)          │
└─────────────────────┬───────────────────────┘
                      │
┌─────────────────────▼───────────────────────┐
│                  Services                    │
│            (Business logic)                  │
└─────────────────────┬───────────────────────┘
                      │
┌─────────────────────▼───────────────────────┐
│               Repositories                   │
│            (Data access)                     │
└─────────────────────┬───────────────────────┘
                      │
┌─────────────────────▼───────────────────────┐
│                  Models                      │
│            (Eloquent entities)               │
└─────────────────────────────────────────────┘
```

### Frontend (React)
```
┌─────────────────────────────────────────────┐
│                   Pages                      │
│            (Route components)                │
└─────────────────────┬───────────────────────┘
                      │
┌─────────────────────▼───────────────────────┐
│                Components                    │
│         (UI building blocks)                 │
└─────────────────────┬───────────────────────┘
                      │
┌─────────────────────▼───────────────────────┐
│              Hooks + State                   │
│     (Logic, API calls, state management)     │
└─────────────────────────────────────────────┘
```

## API Design

### REST Conventions
| Action | Method | Endpoint | Response |
|--------|--------|----------|----------|
| List | GET | /resources | 200 + array |
| Create | POST | /resources | 201 + object |
| Read | GET | /resources/{id} | 200 + object |
| Update | PUT | /resources/{id} | 200 + object |
| Patch | PATCH | /resources/{id} | 200 + object |
| Delete | DELETE | /resources/{id} | 204 |

### Response Format
```json
{
  "data": {},
  "meta": { "pagination": {} },
  "errors": []
}
```

## ADR Template
Use `references/adr-template.md` for architectural decisions.

## Database Design
- Use UUIDs for public IDs
- Soft deletes for user data
- Timestamps on all tables
- Index foreign keys
- Document relationships

## Output Files
- ADRs: `docs/decisions/ADR-XXX-title.md`
- API specs: `docs/architecture/api/`
- Schema: `docs/architecture/database/`

## References
- ADR template: `references/adr-template.md`
- API conventions: `references/api-conventions.md`
