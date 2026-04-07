---
name: filament-v5
description: Filament v5 admin panel best practices. Building admin panels, CRUD resources, forms, tables, and widgets with Filament.
license: MIT
---

## Filament v5 Best Practices

### When to use
- Building admin panels, CRUD resources
- Creating forms, tables, widgets
- Filament actions, filters, pages
- Relationship management in Filament

### Key Patterns
- Always use static `make()` methods to initialize components
- Use `Get $get` for conditional form field visibility
- Use `state()` with Closure for computed table columns
- Actions encapsulate button + modal form + logic
- Always authenticate before testing panel functionality
- Use `Livewire::test()` or `livewire()` for testing

### Correct Namespaces
- Form fields: `Filament\Forms\Components\`
- Infolist entries: `Filament\Infolists\Components\`
- Layout components: `Filament\Schemas\Components\`
- Schema utilities: `Filament\Schemas\Components\Utilities\`
- Actions: `Filament\Actions\` (never `Filament\Tables\Actions\` or sub-namespaces)
- Icons: `Filament\Support\Icons\Heroicon` enum

### Common Mistakes
- Never assume public file visibility (default is private)
- Never assume full-width layout (Grid, Section, Fieldset don't span all columns by default)
