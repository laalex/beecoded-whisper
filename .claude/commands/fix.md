# /fix - Bug Fixer with TDD

Fix bugs using Test-Driven Development approach.

## Usage
```
/fix $ARGUMENTS
```

## Bug Description
$ARGUMENTS

---

## Execution Protocol

This command fixes bugs with a **TDD approach**: reproduce the bug with a test first, then fix it.

### Phase 1: Bug Analysis

Report: "🔍 Analyzing bug..."

1. **Understand the bug**:
   - What is the expected behavior?
   - What is the actual behavior?
   - What are the reproduction steps?

2. **Locate relevant code**:
   - Search for related files
   - Identify the component(s) involved
   - Check existing tests

3. **Load skill**: Use the Skill tool to load `testing-tdd`

### Phase 2: Write Failing Test

Report: "🧪 Writing test to reproduce bug..."

1. **Create test case** that:
   - Reproduces the exact bug scenario
   - Asserts the expected (correct) behavior
   - Is named descriptively (e.g., `it_should_handle_special_characters_in_email`)

2. **Run the test**:
   - Confirm it FAILS
   - The failure should match the bug description
   - If test passes, reassess the bug understanding

### Phase 3: Fix Implementation

Report: "🔧 Implementing fix..."

1. **Determine fix location**:
   - Backend: Load `backend-laravel` skill
   - Frontend: Load `frontend-react` skill

2. **Implement minimal fix**:
   - Fix ONLY the bug
   - Avoid refactoring unrelated code
   - Keep changes focused

3. **Run the test**:
   - Confirm it PASSES
   - If still failing, iterate on fix

### Phase 4: Regression Check

Report: "✅ Running regression tests..."

1. **Run full test suite**:
   - All existing tests must still pass
   - If any break, adjust fix or update tests

2. **Verify no side effects**:
   - Check related functionality
   - Test edge cases

### Phase 5: Quality Check

1. **Check file sizes**:
   - If fix pushed file > 250 lines, split it

2. **Run linting**:
   - Ensure code style compliance

### Phase 6: Documentation

Report: "📝 Documenting fix..."

1. **Load skill**: Use the Skill tool to load `documentation`

2. **Update documentation**:
   - Add to changelog if significant
   - Update relevant docs if behavior changed
   - Comment on complex fixes

### Phase 7: Summary Report

Provide summary:
```
## Bug Fix Summary

**Bug**: [Description]
**Root Cause**: [What caused it]
**Fix**: [What was changed]

### Files Modified
- `path/to/file.php` - [What changed]

### Tests
- Added: `tests/path/to/test.php::test_name`
- All tests passing: ✅

### Verification
- Bug reproduced: ✅
- Bug fixed: ✅
- No regressions: ✅
```

---

## Mandatory Rules
- **MUST write failing test first** before fixing
- **MUST run test after fix** to confirm it passes
- **MUST run full test suite** to check for regressions
- Keep fixes minimal and focused
- Document complex fixes

## TDD Bug Fix Flow
```
1. Write test → FAIL (reproduces bug)
2. Fix code → PASS (bug fixed)
3. Run suite → ALL PASS (no regressions)
```

## Self-Correction
- If you can't reproduce the bug with a test, ask for more details
- If fix breaks other tests, investigate root cause
- If fix is too complex, consider if refactoring is needed (use `/improve` after)
