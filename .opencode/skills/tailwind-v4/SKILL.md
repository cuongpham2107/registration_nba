---
name: tailwind-v4
description: Tailwind CSS v4 best practices for styling. Writing CSS, styling components, building layouts with Tailwind v4.
license: MIT
---

## Tailwind CSS v4 Best Practices

### When to use
- Writing CSS, styling components
- Building layouts with Tailwind v4
- Utility classes, custom themes
- @theme directive usage
- Dark mode, responsive design

### Key Features v4
- `@theme` directive for custom design tokens
- CSS-first configuration (no tailwind.config.js)
- Automatic content detection
- Native cascade layers support
- Improved performance

### Patterns
- Use utility classes directly in templates
- Compose with `@apply` in CSS when needed
- Use `@theme` for custom colors, fonts, spacing
- Responsive prefixes: `sm:`, `md:`, `lg:`, `xl:`, `2xl:`
- Dark mode: `dark:`
- Hover/focus states: `hover:`, `focus:`, `active:`

### Best Practices
- Prefer utility classes over custom CSS
- Extract repeated patterns into components
- Use CSS custom properties for dynamic values
- Keep responsive design mobile-first
- Use `@layer` for custom utilities
