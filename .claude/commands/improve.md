# /improve - Safe Refactoring

Refactor code safely with test coverage protection.

## Usage
```
/improve $ARGUMENTS
```

## Improvement Target
$ARGUMENTS

---

## Execution Protocol

This command performs **safe refactoring** with test protection. Code behavior must remain unchanged.

### Phase 1: Analysis

Report: "🔍 Analyzing code for improvements..."

1. **Load skill**: Use the Skill tool to load `refactoring`

2. **Identify the target**:
   - Locate the file(s)/class(es) to improve
   - Understand current functionality
   - Check file sizes and complexity

3. **Assess improvement type**:
   - File too large (> 250 lines)
   - Code duplication
   - Complex logic needing simplification
   - Poor naming
   - Missing abstractions
   - Performance issues

### Phase 2: Safety Net

Report: "🧪 Verifying test coverage..."

1. **Load skill**: Use the Skill tool to load `testing-tdd`

2. **Check existing tests**:
   - Find tests for the target code
   - Run them to confirm they pass

3. **If tests are missing**:
   - **STOP and write tests first**
   - Tests must cover all existing behavior
   - Run tests to confirm they pass
   - Only then proceed to refactoring

4. **Document current behavior**:
   - Note all public methods/APIs
   - Record expected inputs/outputs

### Phase 3: Refactoring Plan

Report: "📋 Planning refactoring..."

Create refactoring plan:
- What changes will be made
- Expected outcome
- Files to be created/modified
- Risk assessment

### Phase 4: Execute Refactoring

Report: "🔧 Refactoring..."

1. **Apply changes incrementally**:
   - Small, focused changes
   - Run tests after each change
   - Commit mentally (can revert)

2. **Common refactoring patterns**:

   **For large files (> 250 lines):**
   - Extract classes/components
   - Split into focused modules
   - Move related code together

   **For duplication:**
   - Extract shared functions
   - Create base classes/hooks
   - Use composition

   **For complexity:**
   - Extract methods
   - Simplify conditionals
   - Use early returns

   **For poor structure:**
   - Rename for clarity
   - Reorganize file structure
   - Apply design patterns

3. **Run tests after EVERY change**:
   - Tests must stay green
   - If tests fail, revert and reassess

### Phase 5: Quality Verification

Report: "✅ Verifying quality..."

1. **Run full test suite**:
   - All tests must pass
   - No regressions allowed

2. **Check improvements**:
   - File sizes < 250 lines
   - Reduced duplication
   - Improved readability
   - Better organization

3. **Run linting**:
   - Code style compliance

4. **Type checking**:
   - No type errors introduced

### Phase 6: Documentation

Report: "📝 Updating documentation..."

1. **Load skill**: Use the Skill tool to load `documentation`

2. **Update if needed**:
   - API docs if interfaces changed
   - Code comments for complex logic
   - Architecture docs if structure changed

### Phase 7: Summary Report

Provide detailed summary:
```
## Refactoring Summary

**Target**: [What was refactored]
**Reason**: [Why it needed improvement]

### Changes Made
- [Change 1]
- [Change 2]

### Files
| File | Action | Before | After |
|------|--------|--------|-------|
| `old/path.php` | Split | 450 lines | - |
| `new/path1.php` | Created | - | 180 lines |
| `new/path2.php` | Created | - | 150 lines |

### Metrics
- Lines reduced: X → Y
- Files split: N
- Duplication removed: X instances

### Tests
- Before: X passing
- After: X passing (no regressions)

### Quality
- All files < 300 lines: ✅
- Linting passed: ✅
- Type check passed: ✅
```

---

## Mandatory Rules
- **NEVER refactor without tests** - Write them first if missing
- **Tests must stay green** - Run after every change
- **Behavior must not change** - Refactoring is internal only
- **Keep changes incremental** - Small steps, verify each
- **File size limits** - All files must be < 300 lines after

## Red Flags (Stop and Reconsider)
- Tests start failing
- Changes affect public API
- Refactoring scope keeps growing
- No clear improvement visible

## Refactoring vs. Feature Change
- **Refactoring**: Internal code structure changes, same behavior
- **Feature change**: Different behavior, use `/do` instead
