---
name: testing-tdd
description: Test-driven development workflow, writing unit tests, integration tests, and end-to-end tests. Use when writing tests before implementation, setting up test infrastructure, or ensuring code coverage.
version: PHPUnit 11, Vitest 2, Playwright 1.48+, Testing Library 16
---

# Testing TDD Skill

## Version Requirements
- **PHPUnit**: 11.x (Laravel 12 default)
- **Pest**: 3.x (optional, Laravel-friendly)
- **Vitest**: 2.x
- **Playwright**: 1.48+
- **Testing Library**: 16.x
- **MSW**: 2.x (API mocking)

## TDD Workflow

```
┌─────────────────────────────────────────────────────┐
│                  RED-GREEN-REFACTOR                 │
├─────────────────────────────────────────────────────┤
│                                                     │
│   ┌─────────┐     ┌─────────┐     ┌─────────────┐  │
│   │   RED   │────▶│  GREEN  │────▶│  REFACTOR   │  │
│   │  Write  │     │ Minimum │     │   Clean up  │  │
│   │ failing │     │  code   │     │  keep tests │  │
│   │  test   │     │ to pass │     │    green    │  │
│   └─────────┘     └─────────┘     └─────────────┘  │
│        ▲                                │          │
│        └────────────────────────────────┘          │
│                                                     │
└─────────────────────────────────────────────────────┘
```

## Test Structure

```
tests/
├── unit/
│   ├── backend/
│   │   ├── Services/
│   │   ├── Models/
│   │   └── Repositories/
│   └── frontend/
│       ├── components/
│       └── hooks/
├── integration/
│   ├── api/
│   └── database/
└── e2e/
    └── flows/
```

## Test Types

### Unit Tests
- Test single unit in isolation
- Mock all dependencies
- Fast execution
- High coverage

```php
// Laravel Unit Test
public function test_calculates_order_total(): void
{
    $calculator = new OrderCalculator();
    
    $total = $calculator->calculate([
        ['price' => 100, 'quantity' => 2],
        ['price' => 50, 'quantity' => 1],
    ]);
    
    $this->assertEquals(250, $total);
}
```

```tsx
// React Unit Test (Vitest 2)
import { describe, it, expect } from 'vitest';

describe('formatCurrency', () => {
  it('formats currency correctly', () => {
    expect(formatCurrency(1234.56)).toBe('$1,234.56');
  });
});
```

### Integration Tests
- Test component interactions
- Real database (test instance)
- API endpoint testing

```php
// Laravel Feature Test
public function test_creates_user_via_api(): void
{
    $response = $this->postJson('/api/users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
    ]);
}
```

### E2E Tests
- Full user flows
- Real browser (Playwright 1.48+)
- Critical paths only

```typescript
// Playwright 1.48+ E2E
import { test, expect } from '@playwright/test';

test.describe('Checkout Flow', () => {
  test('user can complete checkout', async ({ page }) => {
    await page.goto('/products');
    await page.getByTestId('product-1').click();
    await page.getByTestId('add-to-cart').click();
    await page.getByTestId('checkout').click();
    await page.getByLabel('Email').fill('test@example.com');
    await page.getByTestId('place-order').click();
    await expect(page.getByTestId('order-confirmation')).toBeVisible();
  });
});
```

## Test Patterns

### Arrange-Act-Assert
```php
public function test_example(): void
{
    // Arrange
    $user = User::factory()->create();
    
    // Act
    $result = $user->hasVerifiedEmail();
    
    // Assert
    $this->assertFalse($result);
}
```

### Given-When-Then (BDD)
```php
public function test_registered_user_can_login(): void
{
    // Given a registered user
    $user = User::factory()->create(['password' => bcrypt('password')]);
    
    // When they attempt to login
    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);
    
    // Then they receive a token
    $response->assertOk();
    $response->assertJsonStructure(['token']);
}
```

## Test Naming

```php
// Method: test_[action]_[condition]_[expectation]
test_creates_user_when_valid_data_provided()
test_returns_error_when_email_invalid()
test_user_can_update_profile()
```

## Mocking

### Laravel
```php
// Mock service
$this->mock(PaymentService::class, function ($mock) {
    $mock->shouldReceive('charge')
        ->once()
        ->andReturn(true);
});
```

### React (Vitest 2 + MSW 2)
```tsx
// Mock API with MSW 2
import { http, HttpResponse } from 'msw';
import { setupServer } from 'msw/node';

const server = setupServer(
  http.get('/api/users/:id', ({ params }) => {
    return HttpResponse.json({ id: params.id, name: 'John' });
  })
);

beforeAll(() => server.listen());
afterEach(() => server.resetHandlers());
afterAll(() => server.close());

// Or simple vi.mock
vi.mock('@/api/users', () => ({
  getUser: vi.fn().mockResolvedValue({ id: '1', name: 'John' }),
}));
```

## Coverage Target

- Overall: 80%+
- Critical paths: 100%
- Unit tests: ~60%
- Integration: ~30%
- E2E: ~10%

## Running Tests

```bash
# Laravel (PHPUnit 11 / Pest 3)
php artisan test
php artisan test --coverage
php artisan test --parallel

# React (Vitest 2)
npm test
npm run test:coverage
npm run test:ui  # Vitest UI

# E2E (Playwright 1.48+)
npx playwright test
npx playwright test --ui  # Playwright UI mode
npx playwright show-report
```

## Test Configuration

### vitest.config.ts
```typescript
import { defineConfig } from 'vitest/config';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  test: {
    environment: 'jsdom',
    globals: true,
    setupFiles: ['./src/test/setup.ts'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
    },
  },
});
```

### playwright.config.ts
```typescript
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: true,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: 'html',
  use: {
    baseURL: 'http://localhost:3000',
    trace: 'on-first-retry',
  },
  projects: [
    { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
    { name: 'firefox', use: { ...devices['Desktop Firefox'] } },
    { name: 'webkit', use: { ...devices['Desktop Safari'] } },
  ],
});
```

## Before Committing

1. All tests pass
2. No skipped tests
3. Coverage not decreased
4. New code has tests
