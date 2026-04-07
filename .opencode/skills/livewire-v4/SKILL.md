---
name: livewire-v4
description: Livewire v4 single-file components (SFC) best practices. Creating Livewire v4 components with inline PHP+Blade templates.
license: MIT
---

## Livewire v4 Best Practices

### When to use
- Creating Livewire v4 components
- Full-page Livewire components
- Component state management
- Form handling and validation
- Component communication

### Key Patterns
- Single-file components (SFC) with inline PHP+Blade
- Use `#[Title('...')]` for page titles
- Properties should be public and typed
- Use `#[Validate]` attribute for validation
- Use `#[Modelable]` for two-way binding with parent
- Public methods are callable from frontend
- Use `wire:model.live` for real-time updates
- Use `wire:model.blur` for input on blur

### Lifecycle Hooks
- `mount()` - Component initialization
- `boot()` - Called on every request
- `updating()` / `updated()` - Property change hooks
- `rendering()` / `rendered()` - View rendering hooks

### Communication
- Parent-child: `wire:model` and `#[Modelable]`
- Sibling: Event dispatching with `$this->dispatch()`
- Listen to events with `#[On('event-name')]`

### Performance
- Use `wire:loading` for loading states
- Implement `#[Lazy]` for lazy-loaded components
- Avoid N+1 queries in render method
- Use `wire:navigate` for SPA-like navigation
