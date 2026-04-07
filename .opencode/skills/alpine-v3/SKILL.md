---
name: alpine-v3
description: Alpine.js v3 best practices for building reactive interfaces. Adding client-side interactivity, managing component state, interactive UI elements.
license: MIT
---

## Alpine.js v3 Best Practices

### When to use
- Adding client-side interactivity
- Managing component state
- Creating interactive UI elements
- Modals, dropdowns, tabs, animations
- Enhancing Livewire components

### Core Directives
- `x-data` - Component state declaration
- `x-show` / `x-if` - Conditional rendering
- `x-for` - List rendering with `:key`
- `x-on` / `@` - Event handling
- `x-bind` / `:` - Attribute binding
- `x-model` - Two-way data binding
- `x-text` / `x-html` - Text content binding
- `x-transition` - CSS transitions
- `x-cloak` - Hide unprocessed elements

### Integration with Livewire
- Access Livewire via `$wire` magic property
- Call methods: `$wire.methodName()`
- Access properties: `$wire.propertyName`
- Listen to events: `x-on:livewire:event`
- Dispatch: `$wire.$emit('event')`
- Two-way binding: `$wire.entangle()`

### Best Practices
- Use `x-cloak` with CSS: `[x-cloak] { display: none !important; }`
- Add `:key` to `x-for` loops for proper diffing
- Use `@keydown.escape.window` for global escape handling
- Debounce with `.debounce` modifier
- Keep `x-data` objects small and focused

### When to use Alpine vs Livewire
- **Alpine**: Client-only UI state (dropdowns, modals, tabs, animations)
- **Livewire**: Server-side state, data operations, form submissions
- **Combined**: Complex interactions needing both client and server state
