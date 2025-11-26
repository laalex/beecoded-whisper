# /build - Feature Builder

Build a complete feature with full-stack implementation.

## Usage
```
/build $ARGUMENTS
```

## Feature Request
$ARGUMENTS

---

## Execution Protocol

This command builds **complete features** requiring full-stack implementation. It follows a stricter, more comprehensive workflow than `/do`.

### Phase 1: Feature Specification

1. **Create feature spec** in `docs/features/[feature-name].md`:
   - Overview and goals
   - User stories
   - Acceptance criteria
   - Technical requirements
   - API design
   - Database schema
   - UI/UX requirements

2. **Load skill**: Use the Skill tool to load `project-planning`

### Phase 2: Architecture Design

1. **API Design**:
   - Define all endpoints
   - Request/response schemas
   - Authentication requirements
   - Error handling

2. **Database Design**:
   - Entity relationships
   - Migration plan
   - Index strategy

3. **Create ADR** if this is a significant architectural decision

4. **Load skill**: Use the Skill tool to load `architecture`

### Phase 3: Backend Implementation (TDD)

Report: "🧪 Writing backend tests..."

1. **Load skill**: Use the Skill tool to load `testing-tdd`
2. **Write tests FIRST**:
   - Unit tests for services/repositories
   - Integration tests for API endpoints
   - Run tests (expect failure)

Report: "💻 Implementing backend..."

3. **Load skill**: Use the Skill tool to load `backend-laravel`
4. **Implement**:
   - Migrations
   - Models with relationships
   - Services (business logic)
   - Repositories (data access)
   - Controllers (thin, delegate to services)
   - Form Requests (validation)
   - API Resources (transformation)
5. **Run tests** (expect success)

### Phase 4: Frontend Implementation (TDD)

Report: "🧪 Writing frontend tests..."

1. **Write tests FIRST**:
   - Component tests
   - Hook tests
   - Integration tests
   - Run tests (expect failure)

Report: "💻 Implementing frontend..."

2. **Load skill**: Use the Skill tool to load `frontend-react`
3. **Load skill**: Use the Skill tool to load `ui-ux-guidelines`
4. **Implement**:
   - TypeScript types/interfaces
   - API client functions
   - React Query hooks
   - Components (presentational)
   - Pages (container)
   - State management if needed
5. **Run tests** (expect success)

### Phase 5: Quality Assurance

Report: "✅ Running quality checks..."

1. **Run all tests** (backend + frontend)
2. **Check file sizes**:
   - Any file > 250 lines must be split
3. **Run linting**:
   - PHP: `./vendor/bin/pint`
   - JS/TS: `npm run lint`
4. **Type checking**:
   - TypeScript: `npm run type-check`

### Phase 6: Documentation

Report: "📝 Updating documentation..."

1. **Load skill**: Use the Skill tool to load `documentation`
2. **Update**:
   - Feature spec status → "Completed"
   - API documentation
   - Component documentation (if complex)
   - CLAUDE.md sprint status

### Phase 7: Summary Report

Provide comprehensive summary:
- Feature overview
- Files created (grouped by type)
- Files modified
- Database changes
- API endpoints added
- Test coverage
- Quality check results
- Any follow-up items

---

## Mandatory Rules
- Full feature specs required before implementation
- TDD for both backend and frontend
- File size limits strictly enforced
- All quality gates must pass
- Documentation must be complete

## Checklist
- [ ] Feature spec created
- [ ] Architecture designed
- [ ] Backend tests written and passing
- [ ] Backend implemented
- [ ] Frontend tests written and passing
- [ ] Frontend implemented
- [ ] All files < 300 lines
- [ ] Linting passed
- [ ] Documentation updated
