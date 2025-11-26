---
name: frontend-react
description: React 19+ frontend development with TypeScript 5.6+ and Tailwind CSS 4. Use when creating components, pages, hooks, state management, API integration, or any React-specific development.
version: React 19.x, TypeScript 5.6+, Tailwind CSS 4, Vite 6
---

# Frontend React Skill

## Version Requirements
- **React**: 19.x
- **TypeScript**: 5.6+
- **Tailwind CSS**: 4.0+
- **Vite**: 6.0+
- **Node.js**: 22 LTS
- **TanStack Query**: 5.x (for server state)
- **Zustand**: 5.x (for client state)

## Key React 19 Features
- **React Compiler**: Automatic memoization (no more manual useMemo/useCallback)
- **Server Components**: Simplified data fetching
- **Actions**: Form handling with `useActionState`
- **`use` hook**: Async resource consumption
- **View Transitions API**: Smooth page transitions

## Workflow (TDD)

1. **Read Spec** → Check feature spec and UI/UX guidelines
2. **Write Test** → Component test first (Vitest + Testing Library)
3. **Implement** → Build component with Tailwind 4
4. **Connect** → Integrate with TanStack Query
5. **Polish** → Accessibility, responsive design

## Directory Structure

```
src/frontend/
├── src/
│   ├── components/
│   │   ├── ui/              # Reusable UI components
│   │   └── features/        # Feature-specific components
│   ├── pages/               # Route components
│   ├── hooks/               # Custom hooks
│   ├── api/                 # API client and queries
│   ├── stores/              # State management
│   ├── types/               # TypeScript types
│   ├── utils/               # Helper functions
│   └── __tests__/           # Test files
├── public/
└── index.html
```

## Code Standards

### Components
- Functional components only
- TypeScript strict mode
- Props interface defined
- Single responsibility

```tsx
// Good
interface UserCardProps {
  user: User;
  onEdit?: (id: string) => void;
}

export function UserCard({ user, onEdit }: UserCardProps) {
  return (
    <div className="rounded-lg border p-4 shadow-sm">
      <h3 className="text-lg font-semibold">{user.name}</h3>
      <p className="text-gray-600">{user.email}</p>
      {onEdit && (
        <button
          onClick={() => onEdit(user.id)}
          className="mt-2 rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600"
        >
          Edit
        </button>
      )}
    </div>
  );
}
```

### Hooks
- Prefix with `use`
- Single purpose
- Handle loading/error states
- Leverage React 19's `use` hook for async

```tsx
// TanStack Query 5 pattern
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';

export function useUser(id: string) {
  return useQuery({
    queryKey: ['user', id],
    queryFn: () => api.users.get(id),
  });
}

export function useCreateUser() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: api.users.create,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['users'] });
    },
  });
}
```

### React 19 Actions (Form Handling)
```tsx
import { useActionState } from 'react';

function CreateUserForm() {
  const [state, formAction, isPending] = useActionState(
    async (prevState, formData) => {
      const result = await createUser(formData);
      return result;
    },
    null
  );

  return (
    <form action={formAction}>
      <input name="email" type="email" required />
      <button type="submit" disabled={isPending}>
        {isPending ? 'Creating...' : 'Create User'}
      </button>
    </form>
  );
}
```

### API Layer
- Use TanStack Query 5 for server state
- Centralize API calls
- Type all responses with Zod validation

```tsx
// api/users.ts
import { z } from 'zod';

const UserSchema = z.object({
  id: z.string(),
  name: z.string(),
  email: z.string().email(),
});

export const usersApi = {
  list: async () => {
    const res = await fetch('/api/users');
    const data = await res.json();
    return z.array(UserSchema).parse(data);
  },
  get: async (id: string) => {
    const res = await fetch(`/api/users/${id}`);
    return UserSchema.parse(await res.json());
  },
  create: async (data: CreateUserData) => {
    const res = await fetch('/api/users', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data),
    });
    return UserSchema.parse(await res.json());
  },
};
```

### State Management
- TanStack Query 5 for server state
- Zustand 5 for complex client state
- Context for simple shared state (avoid for frequently changing data)

## Styling with Tailwind

- Use Tailwind utilities
- Extract components, not classes
- Responsive: mobile-first
- Dark mode: use `dark:` prefix

```tsx
// Good - component extraction
function Card({ children, className }: CardProps) {
  return (
    <div className={cn("rounded-lg border bg-white p-4 shadow-sm", className)}>
      {children}
    </div>
  );
}

// Avoid - class extraction with @apply
```

## Testing (Vitest 2 + Testing Library 16)

```tsx
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { describe, it, expect, vi } from 'vitest';

// Test wrapper for TanStack Query
const createTestQueryClient = () => new QueryClient({
  defaultOptions: { queries: { retry: false } },
});

const TestWrapper = ({ children }: { children: React.ReactNode }) => (
  <QueryClientProvider client={createTestQueryClient()}>
    {children}
  </QueryClientProvider>
);

describe('UserCard', () => {
  it('shows user name', () => {
    render(
      <UserCard user={{ name: 'John', email: 'john@test.com' }} />,
      { wrapper: TestWrapper }
    );
    expect(screen.getByText('John')).toBeInTheDocument();
  });

  it('calls onEdit when button clicked', async () => {
    const user = userEvent.setup();
    const onEdit = vi.fn();
    
    render(<UserCard user={mockUser} onEdit={onEdit} />, { wrapper: TestWrapper });
    
    await user.click(screen.getByRole('button', { name: /edit/i }));
    expect(onEdit).toHaveBeenCalledWith(mockUser.id);
  });
});
```

## File Limits
- Components: ~150 lines max
- Hooks: ~100 lines max
- Split if larger

## Recommended Dependencies (package.json)
```json
{
  "dependencies": {
    "react": "^19.0.0",
    "react-dom": "^19.0.0",
    "@tanstack/react-query": "^5.0.0",
    "zustand": "^5.0.0",
    "zod": "^3.23.0",
    "clsx": "^2.1.0",
    "tailwind-merge": "^2.5.0"
  },
  "devDependencies": {
    "typescript": "^5.6.0",
    "vite": "^6.0.0",
    "@vitejs/plugin-react": "^4.3.0",
    "tailwindcss": "^4.0.0",
    "vitest": "^2.1.0",
    "@testing-library/react": "^16.0.0",
    "@testing-library/user-event": "^14.5.0",
    "jsdom": "^25.0.0"
  }
}
```

## References
- React 19 docs: https://react.dev
- TanStack Query: https://tanstack.com/query
- Component patterns: `references/component-patterns.md`
- Tailwind 4 guidelines: `references/tailwind.md`
