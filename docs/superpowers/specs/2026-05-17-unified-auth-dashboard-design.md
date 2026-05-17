# Design Spec: Unified Auth, Admin Setup, and Dashboard

**Date:** 2026-05-17
**Status:** Draft
**Topic:** Implementation of admin auto-creation, Laravel Breeze integration, and unified dashboard routing.

## 1. Problem Statement
The current application lacks a web-based authentication system, making it difficult for users to manage their data. Additionally, the dashboard is split into two separate routes, which complicates navigation and maintenance. There is also a need for an automated way to bootstrap an admin account via environment variables.

## 2. Proposed Changes

### 2.1. Admin Auto-Creation
- **Environment**: Add `ADMIN_NAME`, `ADMIN_EMAIL`, and `ADMIN_PASSWORD` to `db.env`.
- **Docker**: Update `docker-compose.yml` to include `db.env` in the `app` service's `env_file` list.
- **Seeder**: Modify `DatabaseSeeder.php` to check for these variables and create an admin user using `User::firstOrCreate()`.

### 2.2. Authentication Integration (Laravel Breeze)
- **Package**: Install `laravel/breeze` with the Blade/Tailwind stack.
- **Forms**: Verify and ensure proper links between `/login` and `/register`.
- **Redirects**: 
    - Change the root route (`/`) to redirect to `/dashboard`.
    - Unauthenticated users accessing `/` or `/dashboard` will be redirected to `/login` by the `auth` middleware.

### 2.3. Unified Dashboard
- **Route**: Consolidate web routes to use a single `/dashboard` endpoint.
- **Controller**: Update `DashboardController@index` to handle role-based data fetching:
    - **Admin**: Fetch all users and their PC statistics.
    - **User**: Fetch only the authenticated user's PCs and activities.
- **View**: Refactor `dashboard.blade.php` to conditionally render content based on `Auth::user()->role`. The `admin/dashboard.blade.php` will be merged or deprecated.

## 3. Technical Implementation Details

### 3.1. Branching Strategy
1. `dev` (base for all changes)
2. `feature/admin-setup` (Admin credentials & Seeder)
3. `feature/auth-breeze` (Breeze installation & Home redirect)
4. `feature/unified-dashboard` (Controller logic & Blade refactoring)

### 3.2. Data Flow
- **Auth**: Session-based (standard Laravel).
- **Admin Setup**: Occurs during `php artisan migrate --seed` (triggered by `docker-entrypoint.sh`).

## 4. Verification Plan
- **Admin Setup**: Check if the admin user exists in the database after container start if `db.env` is populated.
- **Auth**: Manually verify login, registration, and logout flows.
- **Redirects**: Access `/` and verify redirection to `/login` (guest) or `/dashboard` (user).
- **Dashboard**: Log in as admin and user to verify that the correct content is displayed at `/dashboard`.
