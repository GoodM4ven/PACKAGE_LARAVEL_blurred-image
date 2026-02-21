# Manual Boost Package Overrides

This repository is a Laravel package using Testbench / Workbench, so automatic Boost package detection may miss framework-specific guidance. Treat the following guideline set as explicitly enabled for this project.

## Workbench Commands

- Prefer `php workbench/artisan ...` for Laravel / Boost commands in this package repository.
- Use `./laravel-boost-mcp.sh` for Boost MCP startup from package roots.

## Required Guideline Domains

### Laravel
- Follow Laravel conventions for validation, Eloquent relationships, queues, policies, requests, and named routes.
- Use Form Requests for validation and avoid inline validation when possible.

### Livewire
- Use Livewire server-driven state patterns and validate/authorize action inputs.
- Activate `livewire-development` for Livewire tasks.

### Pest + PHPUnit
- Use Pest for new tests by default in this project.
- Respect existing PHPUnit-style tests where already present; do not delete tests without approval.
- Activate `pest-testing` for testing tasks.

### Tailwind CSS + Alpine.js
- Follow Tailwind v4 conventions and existing project styling patterns.
- Use Alpine.js for client-side interactions when needed with Livewire.
- Activate `tailwindcss-development` for styling tasks.

### Pennant
- Use Laravel Pennant conventions for feature flags and scoped activation.
- Activate `pennant-development` for feature-flag tasks.

### Pint
- Run `vendor/bin/pint --dirty --format agent` before finalizing changes.

### Filament
- If a task touches Filament, follow FilamentPHP conventions and verify version-specific behavior with docs before coding.

## Documentation Coverage (Mandatory)

- Use `search-docs` before implementation changes for Laravel ecosystem work.
- Use package filters when relevant, especially:
  - `laravel/framework`
  - `livewire/livewire`
  - `pestphp/pest`
  - `tailwindcss/tailwindcss`
  - `laravel/pennant`
  - `phpunit/phpunit`
  - `laravel/pint`
  - `filament/filament`
  - `alpinejs/alpinejs`

## Test Enforcement

- Every behavioral change must include or update automated tests.
- Run the minimal relevant tests first, then run broader suites when requested.
