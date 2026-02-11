# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel 12 application for managing events displayed across multiple houses (locations). Uses Filament 4 for admin, Livewire Volt for frontend interactivity, and Flux UI (free edition) for components. The UI is in Spanish.

- **Houses**: Locations that display events, identified by slug for public URLs
- **Events**: Global, time-bound events with images/videos. Active status + datetime window determines visibility
- **Images**: Media items (images or YouTube videos) belonging to events, with per-house exclusion via `image_house` pivot table
- **Users**: Assigned to a house, role-based access via Spatie Permissions & Filament Shield
- **Public Display**: Full-screen display pages at `/display/{slug}` showing active event media per house

## Common Commands

```bash
# Start all dev services (server, queue, logs, vite)
composer run dev

# Build frontend assets for production
npm run build

# Run all tests
php artisan test

# Run specific test file or filter by name
php artisan test tests/Feature/DashboardTest.php
php artisan test --filter=testName

# Format code (always run before finalizing changes)
vendor/bin/pint --dirty

# Fresh database with seed data
php artisan migrate:fresh --seed
```

## Architecture & Key Concepts

### Event-Image-House Relationship (Important)

Events are **global** — they are not tied to a single house. Instead, each Image belongs to an Event, and the `image_house` **pivot table tracks exclusions** (not inclusions). An image is visible at all houses *except* those in its exclusion list. This allows one event to display media selectively per house.

- `Event` → `hasMany(Image)`
- `Image` → `belongsToMany(House)` via `image_house` (exclusion pivot)
- `House` → `belongsToMany(Image)` via `image_house` (excluded images)
- `Image::scopeVisibleForHouse($houseId)` — filters by inverse exclusion
- `Event::getImagesForHouse($houseId)` — returns filtered media
- `Event::scopeCurrentlyActive()` — filters active events within time windows
- `NoEventOverlap` rule (`app/Rules/`) prevents overlapping active events

### Models

- **House** (`app/Models/House.php`): name, slug, location, default_image_path
- **Event** (`app/Models/Event.php`): title, description, start_datetime, end_datetime, is_active
- **Image** (`app/Models/Image.php`): type ('image'|'video'), image_path, youtube_url, time_offset, order. Auto-deletes old files from storage on update/delete via booted lifecycle hooks
- **User** (`app/Models/User.php`): belongsTo House, uses Fortify 2FA, Spatie HasRoles

### Filament 4 Resource Organization

Resources use component-based architecture with separate classes:

```
app/Filament/Resources/{ResourceName}/
├── Pages/           # List, Create, Edit, View pages
├── Schemas/         # Form and Infolist schemas (reusable)
├── Tables/          # Table configuration
└── {ResourceName}Resource.php
```

Each resource delegates to `*Form::configure()`, `*Table::configure()`, etc. Follow this pattern for new resources.

Key Filament v4 specifics:
- Layout components (`Grid`, `Section`, `Fieldset`) live in `Filament\Schemas\Components`
- All actions extend `Filament\Actions\Action` (no separate table action classes)
- Icons use `Filament\Support\Icons\Heroicon` enum
- File visibility is `private` by default — this project explicitly sets `public`
- Use `relationship()` on Select/Checkbox components for relationships

### Livewire Volt Components

Volt components use **class-based** syntax extending `Livewire\Volt\Component`:
- Auth views: `resources/views/livewire/auth/`
- Settings: `resources/views/livewire/settings/`
- Dashboard: `resources/views/livewire/dashboard/index.blade.php` — polls every 60s, admin sees all houses, regular users see only their assigned house
- Public display: `resources/views/livewire/display/house-events.blade.php`

### Frontend Stack

- **Flux UI** (free) — available components: avatar, badge, brand, breadcrumbs, button, callout, checkbox, dropdown, field, heading, icon, input, modal, navbar, profile, radio, select, separator, switch, text, textarea, tooltip
- **Tailwind CSS v4** — uses `@import "tailwindcss"` syntax, not `@tailwind` directives
- **Alpine.js** `mediaSlideshow` component (`resources/js/media-slideshow.js`) — handles image/video carousel with YouTube IFrame API integration, auto-advances based on `time_offset`
- Dark mode support using `dark:` classes

### Routes

- `/admin` — Filament admin panel
- `/display/{slug}` — Public display (unauthenticated), uses minimal full-screen layout
- `/dashboard` — Authenticated user dashboard
- `/settings/*` — Profile, password, 2FA, appearance (Volt components)
- Auth routes in `routes/auth.php` via Fortify

### Auth & Authorization

- **Fortify** for authentication (including 2FA)
- **Spatie Permission** + **Filament Shield** for role management
- `HasRole` middleware (`app/Http/Middleware/`) checks for super_admin or any role
- `Gate::before` in AppServiceProvider grants super_admin bypass on all checks
- Policies (`app/Policies/`) delegate to Spatie permission checks

### Broadcasting

- `EventCreated` broadcast event fires on Event create/update via `EventObserver`
- Default broadcast driver is `log` (development); Laravel Reverb available but not configured

## Code Conventions

- **PHP 8.4**: Use constructor property promotion, explicit return types, type hints
- **Casts**: Use `casts()` method on models, not `$casts` property
- **Database**: SQLite by default. When modifying columns in migrations, include ALL attributes or they will be dropped
- **Validation**: Use Form Request classes, not inline controller validation
- **Tests**: Pest (not PHPUnit syntax). Feature tests in `tests/Feature/`, unit in `tests/Unit/`, browser in `tests/Browser/`
- **Comments**: Prefer PHPDoc blocks over inline comments
- **Enums**: Keys should be TitleCase
- **Control structures**: Always use curly braces, even for single-line bodies
- **File uploads**: public disk, `events` directory, max 10MB, PNG/JPEG/JPG/WebP

## Seeder Data

Default admin users created by DatabaseSeeder: Faustino Vasquez, Miguel Torres, Carolina Molina — all with `super_admin` role. ShieldSeeder sets up permissions.
