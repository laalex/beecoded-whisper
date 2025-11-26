---
name: backend-laravel
description: Laravel 12+ backend development with REST APIs, services, repositories, and queue workers. Use when creating models, migrations, controllers, services, API endpoints, queue jobs, or any Laravel-specific development.
version: Laravel 12.x, PHP 8.3+
---

# Backend Laravel Skill

## Version Requirements
- **Laravel**: 12.x
- **PHP**: 8.3+ (8.4 recommended)
- **Composer**: 2.7+

## Key Laravel 12 Features
- Modern starter kits (React, Vue, Livewire)
- Native GraphQL support
- Enhanced Query Builder with `nestedWhere()`
- Async caching and dynamic queue prioritization
- AI-powered debugging tools (optional)

## Workflow (TDD)

1. **Read Spec** → Check `docs/features/` and `docs/architecture/`
2. **Write Test** → Create failing test first
3. **Implement** → Minimum code to pass
4. **Refactor** → Keep tests green
5. **Document** → Update API docs

## Directory Structure

```
src/backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Requests/        # Form validation
│   │   ├── Resources/       # API transformers
│   │   └── Middleware/
│   ├── Models/
│   ├── Services/            # Business logic
│   ├── Repositories/        # Data access
│   ├── Jobs/                # Queue jobs
│   ├── Events/
│   ├── Listeners/
│   └── Exceptions/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── routes/
│   └── api.php
└── tests/
    ├── Unit/
    └── Feature/
```

## Code Standards

### Controllers
- Thin controllers (delegate to services)
- Use Form Requests for validation
- Use Resources for response transformation
- RESTful naming

```php
// Good
class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    public function store(StoreUserRequest $request): UserResource
    {
        $user = $this->userService->create($request->validated());
        return new UserResource($user);
    }
}
```

### Services
- Business logic here
- One public method per action
- Inject repositories

### Repositories
- Data access layer
- Return Eloquent models or collections
- No business logic

### Form Requests
```php
class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
        ];
    }
}
```

### API Resources
```php
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
```

## Testing

Always write tests first:
```php
public function test_can_create_user(): void
{
    $response = $this->postJson('/api/users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['data' => ['id', 'name', 'email']]);
}
```

## File Limits
- Controllers: ~100 lines max
- Services: ~200 lines max
- Models: ~150 lines max
- Split if larger

## Laravel 12 Specific Patterns

### Typed Properties (PHP 8.3+)
```php
class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly CacheManager $cache,
    ) {}
}
```

### Enums for Status
```php
enum UserStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';
}
```

### Modern Attribute Syntax
```php
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(UserObserver::class)]
class User extends Model
{
    // ...
}
```

## References
- Laravel 12 docs: https://laravel.com/docs/12.x
- Laravel conventions: `references/laravel-conventions.md`
- API response format: `references/api-format.md`
