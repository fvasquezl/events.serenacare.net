# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 application for managing events across different houses. The application uses Filament 4 for admin panel management, Livewire Volt for frontend interactivity, and Flux UI for components. It includes:

- **House Management**: Houses are locations that can host events
- **Event Management**: Events belong to houses and have start/end times, images, and active status
- **User Management**: Users can belong to houses and have role-based permissions (via Spatie Permissions & Filament Shield)
- **Public Display**: Public-facing display pages for showing active events per house

## Common Commands

### Development
```bash
# Start all development services (server, queue, logs, vite)
composer run dev

# Start just the Laravel server
php artisan serve

# Start Vite for frontend assets
npm run dev

# Build frontend assets for production
npm run build
```

### Testing
```bash
# Run all tests
php artisan test

# Run a specific test file
php artisan test tests/Feature/DashboardTest.php

# Filter tests by name
php artisan test --filter=testName
```

### Code Quality
```bash
# Format code with Pint (always run before finalizing changes)
vendor/bin/pint --dirty

# Run Pint without test flag
vendor/bin/pint
```

### Database
```bash
# Run migrations
php artisan migrate

# Fresh migrations with seeding
php artisan migrate:fresh --seed
```

## Architecture & Structure

### Models & Relationships

- **House**: Central entity representing a location
  - `hasMany` Events
  - `hasMany` Users
  - Has `slug` for public URL access
  - Has computed `active_event` attribute for currently active event

- **Event**: Time-bound events belonging to houses
  - `belongsTo` House
  - Has image uploads stored in `storage/app/public/events`
  - Method `isCurrentlyActive()` checks if event is active and within time window

- **User**: Authentication with house assignment
  - `belongsTo` House
  - Uses Fortify for authentication (including 2FA)
  - Uses Spatie Permissions for role management
  - Method `initials()` generates user initials from name

### Filament 4 Resource Organization

Filament resources use a **component-based architecture** with separate classes for different concerns:

```
app/Filament/Resources/{ResourceName}/
├── Pages/           # List, Create, Edit, View pages
├── Schemas/         # Form and Infolist schemas (reusable)
├── Tables/          # Table configuration
└── {ResourceName}Resource.php  # Main resource class
```

**Example**: `UserResource` delegates to:
- `UserForm::configure()` for form schema
- `UserInfolist::configure()` for view schema
- `UsersTable::configure()` for table configuration

This pattern promotes reusability and separation of concerns. Follow this pattern for new resources.

### Livewire & Volt

- Volt components use **class-based** syntax extending `Livewire\Volt\Component`
- Authentication views are Volt components in `resources/views/livewire/auth/`
- Settings pages are Volt components in `resources/views/livewire/settings/`
- Use `php artisan make:volt [name]` to create new Volt components

### Frontend Stack

- **Flux UI** (free edition) for UI components - check available components before creating custom ones
- **Tailwind CSS v4** - uses `@import "tailwindcss"` syntax, not `@tailwind` directives
- Dark mode support using `dark:` classes
- Vite for asset bundling

### Routes & Public Display

- Admin panel at `/admin` (Filament)
- Public display at `/display/{slug}` - shows active event for a house by slug
- Authentication routes handled by Laravel Fortify
- Settings routes for user profile, password, 2FA, appearance

### Validation

- Use Form Request classes for validation (not inline controller validation)
- Check sibling Form Requests to match array vs string-based validation conventions

### Testing Strategy

- Tests use **Pest** (not PHPUnit syntax)
- Browser tests should go in `tests/Browser/` (Pest v4 browser testing available)
- Feature tests in `tests/Feature/`, Unit tests in `tests/Unit/`
- Use factories for model creation in tests
- Run minimal tests with filters during development: `php artisan test --filter=testName`

## File Upload Configuration

Events use file uploads with these settings:
- Disk: `public`
- Directory: `events`
- Visibility: `public`
- Max size: 10MB
- Accepted types: PNG, JPEG, JPG, WebP

## Database

- Uses **SQLite** by default
- Uses `casts()` method on models (not `$casts` property) - follow existing conventions
- When modifying columns in migrations, include ALL attributes or they will be dropped

## Important Patterns

### Filament Schema Components
- Layout components moved to `Filament\Schemas\Components` in v4 (Grid, Section, Fieldset, etc.)
- All actions extend `Filament\Actions\Action` (no separate table action classes)
- Use `relationship()` method on Select/Checkbox components when working with relationships

### Heroicons
- Use `Filament\Support\Icons\Heroicon` enum for icons (see `UserResource.php`)

### Two-Factor Authentication
- Managed through Fortify
- UI in `resources/views/livewire/settings/two-factor.blade.php`
- Recovery codes view in subdirectory

## Role & Permissions

- Uses **Spatie Laravel Permission** package
- Uses **Filament Shield** for admin panel role management
- Shield resources available at `/admin/shield/roles`
