---
name: refactoring
description: Code quality improvement, refactoring workflows, and predictive maintenance. Use when analyzing code for improvements, splitting large files, extracting methods, or addressing technical debt without changing functionality.
---

# Refactoring Skill

## Core Rules

```
┌─────────────────────────────────────────────────────────────────┐
│                     REFACTORING RULES                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. NEVER change functionality                                  │
│  2. NEVER break existing tests                                  │
│  3. ALWAYS commit before refactoring                            │
│  4. ALWAYS run tests after each change                          │
│  5. SMALL incremental changes only                              │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

## Refactoring Workflow

1. **Analyze** → Identify code smells
2. **Test** → Ensure tests exist and pass
3. **Commit** → Save current state
4. **Refactor** → Make ONE change
5. **Test** → Verify tests still pass
6. **Repeat** → Continue until done

## Code Smells to Address

### File Size
- **Threshold**: 250-300 lines max
- **Action**: Split into smaller modules
- **Priority**: High

### Method/Function Size
- **Threshold**: 30 lines max
- **Action**: Extract methods
- **Priority**: High

### Nesting Depth
- **Threshold**: 3 levels max
- **Action**: Early returns, extract methods
- **Priority**: Medium

### Duplicate Code
- **Threshold**: 3+ occurrences
- **Action**: Extract to shared function
- **Priority**: High

### God Classes
- **Signs**: Does too many things
- **Action**: Split by responsibility
- **Priority**: High

### Long Parameter Lists
- **Threshold**: 4+ parameters
- **Action**: Use parameter object
- **Priority**: Medium

## Refactoring Techniques

### Extract Method
```php
// Before
public function process($order)
{
    // Calculate total
    $total = 0;
    foreach ($order->items as $item) {
        $total += $item->price * $item->quantity;
    }
    // Apply discount
    if ($order->coupon) {
        $total -= $total * $order->coupon->discount;
    }
    return $total;
}

// After
public function process($order)
{
    $total = $this->calculateSubtotal($order->items);
    return $this->applyDiscount($total, $order->coupon);
}

private function calculateSubtotal(array $items): float
{
    return array_reduce($items, fn($sum, $item) => 
        $sum + ($item->price * $item->quantity), 0);
}

private function applyDiscount(float $total, ?Coupon $coupon): float
{
    if (!$coupon) return $total;
    return $total - ($total * $coupon->discount);
}
```

### Split Large File
```
// Before: UserController.php (500 lines)

// After:
UserController.php        (80 lines)  - routing/validation
UserService.php          (150 lines) - business logic
UserRepository.php       (100 lines) - data access
UserTransformer.php       (50 lines) - response formatting
```

### Replace Conditionals with Polymorphism
```php
// Before
public function calculatePrice($type)
{
    if ($type === 'regular') return $this->basePrice;
    if ($type === 'premium') return $this->basePrice * 0.9;
    if ($type === 'vip') return $this->basePrice * 0.8;
}

// After
interface PricingStrategy {
    public function calculate(float $basePrice): float;
}

class RegularPricing implements PricingStrategy { ... }
class PremiumPricing implements PricingStrategy { ... }
class VipPricing implements PricingStrategy { ... }
```

### Early Return
```php
// Before
public function process($user)
{
    if ($user) {
        if ($user->isActive()) {
            if ($user->hasPermission()) {
                // actual logic
            }
        }
    }
}

// After
public function process($user)
{
    if (!$user) return;
    if (!$user->isActive()) return;
    if (!$user->hasPermission()) return;
    
    // actual logic
}
```

## Post-Merge Analysis

After every merge to main:

1. **Run static analysis**
   ```bash
   composer analyze
   npm run lint
   ```

2. **Check file sizes**
   ```bash
   find src -name "*.php" -exec wc -l {} \; | sort -rn | head -20
   ```

3. **Review complexity**
   ```bash
   composer complexity
   ```

4. **Create refactoring tasks**
   - Add to backlog, don't block release
   - Prioritize by impact
   - Schedule in sprints

## Refactoring Safely

### Before Starting
```bash
git status          # Clean working directory
php artisan test    # All tests pass
git checkout -b refactor/description
```

### During Refactoring
```bash
# After each change
php artisan test    # Tests still pass
git add -p          # Review changes
git commit -m "refactor: description"
```

### After Completion
```bash
php artisan test    # Final verification
git diff main       # Review all changes
# Create PR, request review
```

## Metrics to Track

- Cyclomatic complexity
- Lines per file
- Test coverage
- Code duplication %
- Technical debt ratio
