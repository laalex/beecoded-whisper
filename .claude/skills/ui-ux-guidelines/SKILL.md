---
name: ui-ux-guidelines
description: Design system, style guidelines, and UI/UX specifications using Tailwind CSS 4. Use when defining visual design, creating component styles, ensuring design consistency, or implementing accessibility standards.
version: Tailwind CSS 4.0
---

# UI/UX Guidelines Skill

## Tailwind CSS 4 Overview

Tailwind 4 uses a **CSS-first configuration** approach. Configuration is done directly in CSS using `@theme` instead of `tailwind.config.js`.

### CSS-First Configuration (Tailwind 4)

```css
/* app.css */
@import "tailwindcss";

@theme {
  /* Primary colors */
  --color-primary-50: #eff6ff;
  --color-primary-100: #dbeafe;
  --color-primary-500: #3b82f6;
  --color-primary-600: #2563eb;
  --color-primary-700: #1d4ed8;

  /* Neutral */
  --color-gray-50: #f9fafb;
  --color-gray-100: #f3f4f6;
  --color-gray-500: #6b7280;
  --color-gray-900: #111827;

  /* Semantic */
  --color-success: #10b981;
  --color-warning: #f59e0b;
  --color-error: #ef4444;
  --color-info: #3b82f6;

  /* Custom font */
  --font-sans: 'Inter', system-ui, sans-serif;
  --font-mono: 'JetBrains Mono', monospace;
}
```

## Design System

### Typography (Tailwind 4)

```css
/* In @theme block */
@theme {
  --font-sans: 'Inter', system-ui, sans-serif;
  --font-mono: 'JetBrains Mono', monospace;
  
  /* Custom text sizes if needed */
  --text-display: 3.5rem;
  --text-headline: 2.5rem;
}
```

Default Tailwind scale (built-in):
- `text-xs`: 0.75rem (12px)
- `text-sm`: 0.875rem (14px)
- `text-base`: 1rem (16px)
- `text-lg`: 1.125rem (18px)
- `text-xl`: 1.25rem (20px)
- `text-2xl`: 1.5rem (24px)
- `text-3xl`: 1.875rem (30px)

### Spacing

Use Tailwind's spacing scale (multiples of 4px):
- `p-1` = 4px
- `p-2` = 8px
- `p-4` = 16px
- `p-6` = 24px
- `p-8` = 32px

### Border Radius

```
--rounded-sm: 0.125rem;  /* 2px */
--rounded: 0.25rem;      /* 4px */
--rounded-md: 0.375rem;  /* 6px */
--rounded-lg: 0.5rem;    /* 8px */
--rounded-xl: 0.75rem;   /* 12px */
```

## Component Guidelines

### Buttons

```tsx
// Primary
<button className="rounded-lg bg-primary-600 px-4 py-2 text-white hover:bg-primary-700 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
  Primary Action
</button>

// Secondary
<button className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-700 hover:bg-gray-50">
  Secondary
</button>

// Sizes
sm: "px-3 py-1.5 text-sm"
md: "px-4 py-2 text-base"
lg: "px-6 py-3 text-lg"
```

### Forms

```tsx
// Input
<input className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-primary-500 focus:ring-2 focus:ring-primary-500" />

// Label
<label className="mb-1 block text-sm font-medium text-gray-700">

// Error state
<input className="border-red-500 focus:border-red-500 focus:ring-red-500" />
<p className="mt-1 text-sm text-red-600">Error message</p>
```

### Cards

```tsx
<div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
  <h3 className="text-lg font-semibold text-gray-900">Title</h3>
  <p className="mt-2 text-gray-600">Content</p>
</div>
```

## Accessibility Standards

### Required
- Color contrast: 4.5:1 minimum
- Focus indicators: visible outline
- Alt text: all images
- Keyboard navigation: all interactive elements
- ARIA labels: where semantics unclear

### Focus States
```tsx
focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
```

### Screen Reader
```tsx
// Hidden visually but available to screen readers
<span className="sr-only">Description</span>
```

## Responsive Design

### Breakpoints
```
sm: 640px
md: 768px
lg: 1024px
xl: 1280px
2xl: 1536px
```

### Mobile-First Approach
```tsx
// Start with mobile, add larger breakpoints
<div className="p-4 md:p-6 lg:p-8">
<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
```

## Styling with Tailwind 4

- Use Tailwind utilities (new simplified syntax)
- Extract components, not classes
- Responsive: mobile-first
- Dark mode: use `dark:` prefix
- **New in Tailwind 4**: Container queries with `@container`

```tsx
// Good - component extraction with clsx/tailwind-merge
import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

function cn(...inputs: (string | undefined)[]) {
  return twMerge(clsx(inputs));
}

function Card({ children, className }: CardProps) {
  return (
    <div className={cn("rounded-lg border bg-white p-4 shadow-sm", className)}>
      {children}
    </div>
  );
}

// Container queries (Tailwind 4)
function ResponsiveCard() {
  return (
    <div className="@container">
      <div className="@sm:flex @sm:gap-4">
        {/* Responds to container size, not viewport */}
      </div>
    </div>
  );
}
```

## Animation

### Transitions
```tsx
// Standard transition
transition-all duration-200 ease-in-out

// Hover effects
hover:scale-105 transition-transform

// Loading states
animate-pulse
animate-spin
```

## Tailwind 4 Migration Notes

Key changes from Tailwind 3:
1. **CSS-first config**: Use `@theme` in CSS instead of `tailwind.config.js`
2. **No more @tailwind directives**: Use `@import "tailwindcss"` instead
3. **Native CSS variables**: All theme values are CSS custom properties
4. **Built-in container queries**: `@container` and `@sm:`, `@md:`, etc.
5. **Simplified dark mode**: Works out of the box
6. **Faster builds**: New Oxide engine

### Minimal Setup (Tailwind 4)

```css
/* app.css */
@import "tailwindcss";

/* Optional: customize theme */
@theme {
  --color-primary: #3b82f6;
  --font-sans: 'Inter', system-ui, sans-serif;
}
```

```typescript
// vite.config.ts
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  plugins: [react(), tailwindcss()],
});
```

## References
- Tailwind 4 docs: https://tailwindcss.com/docs
- Component library: `references/components.md`
