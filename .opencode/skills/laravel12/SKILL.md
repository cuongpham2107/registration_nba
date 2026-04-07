---
name: laravel12
description: Laravel 12 best practices and conventions. Writing, reviewing, or refactoring Laravel PHP code including controllers, models, migrations, services, Eloquent queries.
license: MIT
---

## Laravel 12 Best Practices

### When to use
- Writing or reviewing Laravel PHP code
- Creating controllers, models, migrations, form requests, policies, jobs, services
- Eloquent queries and relationships
- N+1 query issues, caching strategies, validation, error handling
- Route definitions and architectural decisions

### Key Patterns
- Use Form Requests for validation
- Use Policies for authorization
- Eager load relationships to avoid N+1
- Use Service classes for complex business logic
- Follow single responsibility principle
- Use route/model binding where possible
- Casts should be in `casts()` method, not `$casts` property

### Naming Conventions
- Models: Singular PascalCase (`User`, `ProductCategory`)
- Controllers: Plural PascalCase (`UsersController`)
- Migrations: Plural snake_case (`create_users_table`)
- Services: Singular PascalCase + `Service` suffix (`CreateUserService`)
