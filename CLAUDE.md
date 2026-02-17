# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

REST API backend for "Louer un Chauffeur Prestige" (LCP), a French VTC (chauffeur) booking platform. Built on **Laravel 12** with **Laravel Passport** for OAuth2 authentication. Supports three user types: **client**, **driver** (chauffeur), and **company** (société), plus **admin**.

All API responses use a JSON envelope: `{ "success": bool, "data": ..., "message": "..." }` via `BaseController::sendResponse()` / `sendError()`.

## Development Commands

```bash
# Full dev environment (server + queue + logs + vite)
composer dev

# Or individually:
php artisan serve
php artisan queue:listen --tries=1

# Database
php artisan migrate
php artisan db:seed              # Seeds reference data (user types, trip types, document types, license types, permissions)
php artisan migrate:fresh --seed # Reset and reseed

# Passport setup (required after fresh install)
php artisan passport:install

# Tests
php artisan test
./vendor/bin/phpunit --testsuite=Unit
./vendor/bin/phpunit --testsuite=Feature
./vendor/bin/phpunit tests/Feature/SomeTest.php

# Linting
./vendor/bin/pint              # Laravel Pint (PSR-12 code style)

# API docs (Scramble)
# Available at /docs/api when server is running
```

## Architecture

### Authentication & Authorization

- **Laravel Passport** OAuth2 via `auth:api` guard. Supports password grant and a custom social grant (`SocialGrant`) for Google OAuth.
- Token expiry: 15 minutes access, 6 months refresh (configured in `AppServiceProvider`).
- Email verification is custom: 6-digit codes stored in `email_verification_codes` table, not Laravel's built-in verification.
- Admin access is checked via `User::isAdmin()` which delegates to `UserType::is_admin`. The `AdminMiddleware` gates all `/api/admin/*` routes.
- Authorization policies exist for: `Ride`, `User`, `Company`, `DriverProfile`, `Payment`, `Review`, `Vehicle`.

### Route Structure (`routes/api.php`)

All routes are under `/api/`. Two main groups:
- **Public**: auth (register/login), content pages, vehicle brands/models, ride quotes
- **Authenticated** (`auth:api` + `verified`): rides, users, drivers, companies, payments, reviews, vehicles, notifications
- **Admin** (`auth:api` + `admin` middleware): driver/company verification, pricing rules, user management, statistics, audit logs, settings

Models use **UUID** for public-facing route keys (`getRouteKeyName() => 'uuid'`).

### Controller Pattern

All API controllers extend `App\Http\Controllers\Api\BaseController`, which provides:
- `sendResponse($data, $message)` — 200 JSON success
- `sendError($error, $errorMessages, $code)` — Error JSON response

Admin controllers live in `App\Http\Controllers\Api\Admin\`.

### Key Services (`app/Services/`)

| Service | Purpose |
|---|---|
| `PricingService` | Calculates ride pricing from `PricingRule` records. Handles VAT (20%), commission, long-distance surcharges, round trips. Creates `PricingSnapshot` for audit. |
| `LocationService` | Geocoding (Google Maps or OpenStreetMap/Nominatim), routing (OSRM), Haversine distance. Has `isInFrance()` bounds check. |
| `DriverMatchingService` | Finds nearby available drivers using Haversine SQL query, scores them (rating, proximity, acceptance rate), auto-assigns. |
| `FileStorageService` | File uploads to `public` disk, creates `Media` (polymorphic) and `Document` records. Handles avatars, documents, vehicle images. |
| `NotificationService` | Sends email (via Laravel Mail) + in-app notifications for all platform events (rides, payments, verification, etc.). |

All services are registered as singletons in `AppServiceProvider`.

### Custom Middleware (`app/Http/Middleware/`)

- `ForceJsonResponse` — Forces `Accept: application/json` on all API requests
- `EnsureEmailIsVerified` — Custom `verified` middleware returning JSON 403
- `AdminMiddleware` — Checks `isAdmin()`, returns JSON 403
- `ThrottleApiRequests` — Custom rate limiter with `X-RateLimit-*` headers

### Database Schema

38 domain migrations (`2024_01_01_000001` through `000038`) plus Passport OAuth tables. Lookup/reference tables are seeded via `DatabaseSeeder` (all seeders use `updateOrCreate` so they're idempotent):
- `UserTypeSeeder` — client, driver, company, admin
- `TripTypeSeeder` — 7 French trip types (aller simple, aller-retour, aéroport, gare, etc.)
- `DocumentTypeSeeder` — 12 types across driver/vehicle/company categories
- `LicenseTypeSeeder` — French license categories (B, B+E, C, D, D1)
- `PermissionSeeder` — 33 permissions across 9 groups

### Business Configuration

`config/lcp.php` contains all business rules: commission rates, cancellation fees, pricing defaults, driver matching parameters, payment settings, document limits, feature flags. Most values are overridable via `.env`.

### API Documentation

Uses **Scramble** (`dedoc/scramble`) for auto-generated OpenAPI docs. Controllers use PHPDoc `@tags`, `@authenticated`, `@unauthenticated` annotations. Bearer auth security scheme is configured in `AppServiceProvider`.

### External Integrations

- **Stripe** — Payment processing (configured in `config/services.php`)
- **Google OAuth** — Social login via custom Passport grant
- **Google Maps / OpenStreetMap** — Geocoding and routing (configurable via `USE_OPENSTREETMAP` env)
- **Laravel Mail** — Email notifications (Blade templates in `resources/views/emails/`)

### Key Conventions

- Response messages are in **French** for user-facing endpoints (login, register, etc.)
- All models with public exposure use **UUID** fields for external identification
- The `Media` model is **polymorphic** (`mediable_type`/`mediable_id`) — used for avatars, documents, vehicle images
- Policies are registered manually in `AppServiceProvider::boot()` via `Gate::policy()`
