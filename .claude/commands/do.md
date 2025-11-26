# /do - Main Autonomous Orchestrator

Execute any business request with full autonomous workflow.

## Usage
```
/do $ARGUMENTS
```

## Request
$ARGUMENTS

---

## Execution Protocol

You are now executing the **Autonomous Workflow Engine**. Follow these phases:

### Phase 1: Analysis
1. Parse the request to understand scope and requirements
2. Check if a feature spec exists in `docs/features/`
3. If NO spec exists for new features, create one first
4. Identify affected components (backend/frontend/both)

### Phase 2: Classification
Classify this request as one of:
- **NEW_FEATURE**: "add", "create", "implement", "build"
- **BUG_FIX**: "fix", "bug", "broken", "error"
- **REFACTOR**: "refactor", "clean", "improve", "optimize"
- **MODIFICATION**: "update", "change", "modify"
- **INFRASTRUCTURE**: "setup", "configure", "install"
- **DOCUMENTATION**: "document", "explain", "describe"

### Phase 3: Load Required Skills
Based on classification, load the appropriate skills:
- NEW_FEATURE: `project-planning` → `architecture` → `testing-tdd` → implementation skills → `documentation`
- BUG_FIX: `testing-tdd` → implementation skills → `documentation`
- REFACTOR: `refactoring` → `testing-tdd`
- Use the Skill tool to load each required skill

### Phase 4: Execute Workflow

Report progress using these markers:
- "📋 Creating feature spec..."
- "🏗️ Designing architecture..."
- "🧪 Writing tests..."
- "💻 Implementing..."
- "✅ Running tests..."
- "📝 Updating documentation..."

#### For NEW_FEATURE:
1. Create/verify feature spec in `docs/features/`
2. Design API endpoints if needed
3. Plan database migrations if needed
4. **Write tests FIRST** (TDD is mandatory)
5. Run tests (expect failure)
6. Implement minimum code to pass tests
7. Run tests (expect success)
8. Refactor if needed (keep tests green)
9. Check file sizes (max 250-300 lines)
10. Update documentation

#### For BUG_FIX:
1. Write a test that reproduces the bug
2. Run test (confirm failure)
3. Fix the bug
4. Run test (confirm success)
5. Document the fix

#### For REFACTOR:
1. Ensure tests exist (write if missing)
2. Run tests (confirm passing)
3. Perform refactoring
4. Run tests (must stay green)
5. Report changes

### Phase 5: Quality Gates
Before marking complete, verify:
- [ ] All tests pass
- [ ] No files exceed 300 lines
- [ ] Documentation updated
- [ ] Code follows project patterns

### Phase 6: Report
Provide a summary:
- What was done
- Files created/modified
- Test status
- Any follow-up recommendations

---

## Mandatory Rules
- **TDD is non-negotiable**: Never write implementation without tests first
- **File size enforced**: Split any file > 250 lines before proceeding
- **Documentation required**: Update docs after every task
- **Quality gates**: Must pass all checks before completion

## Self-Correction
If at any point:
- Tests fail → Fix before proceeding
- File too large → Split before proceeding
- Missing spec → Create before implementing
- Missing tests → Write before implementing
- Unclear requirements → Ask ONE clarifying question, then proceed
