# Authentication Flow Updates Design

**Goal:** Improve user experience by redirecting unauthenticated users to the registration page, linking login and registration forms, verifying password reset, and updating documentation.

## 1. Homepage Redirection
- **Current State:** Accessing `/` unconditionally redirects to `/dashboard`. If the user is unauthenticated, the `auth` middleware intercepts and redirects to `/login`.
- **Proposed Change:** Update `routes/web.php` to explicitly check authentication status.
  - If `auth()->check()` is true, redirect to `route('dashboard')`.
  - Otherwise, redirect to `route('register')`.

## 2. Form Navigation
- **Current State:** The registration form (`register.blade.php`) contains a link to the login form, but the login form (`login.blade.php`) lacks a link to the registration form.
- **Proposed Change:** Add a "Not registered yet?" (or similar) link to `resources/views/auth/login.blade.php` pointing to `route('register')`, positioned alongside the "Forgot your password?" link or login button.

## 3. Password Reset & Environment Variables
- **Current State:** Password reset routes and controllers are provided by Laravel Breeze and are correctly mapped in `routes/auth.php`. Mail environment variables are present in `.env.example`.
- **Verification:** The current `MAIL_MAILER` is set to `log`, which is appropriate for local development (emails are written to `storage/logs/laravel.log`). For production or real email delivery, these variables must be configured with SMTP details.
- **Action:** No code changes needed for the implementation, but this requirement ties into the README update.

## 4. Documentation Update (README.md)
- **Current State:** The README provides good setup instructions but lacks details on configuring mail for password resets.
- **Proposed Change:** Add a subsection under "Setup Environment" explaining the `MAIL_*` variables and how to configure them for password recovery to work in a real environment.
