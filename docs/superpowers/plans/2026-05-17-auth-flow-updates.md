# Auth Flow Updates Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Redirect unauthenticated users to the registration page, add a link from the login page to the registration page, and update the README with mail configuration instructions.

**Architecture:** We will update the `web.php` route definition for `/` to conditionally redirect based on authentication status. We will add a simple hyperlink to the blade template for login. We will update the documentation with a new section.

**Tech Stack:** Laravel, Blade, Markdown

---

### Task 1: Update Homepage Redirection

**Files:**
- Modify: `routes/web.php`

- [ ] **Step 1: Update the root route**

Modify the `Route::get('/', function () { ... })` definition in `routes/web.php` to include an authentication check.

```php
<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('register');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
```

- [ ] **Step 2: Commit**

```bash
git add routes/web.php
git commit -m "- updated root route to redirect unauthenticated users to register"
```

### Task 2: Add Registration Link to Login Form

**Files:**
- Modify: `resources/views/auth/login.blade.php`

- [ ] **Step 1: Add the link before the login button**

In `resources/views/auth/login.blade.php`, find the `<div class="flex items-center justify-end mt-4">` section at the bottom of the form and add the registration link alongside the "Forgot your password?" link.

Update the div to look like this:

```html
        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-4" href="{{ route('register') }}">
                {{ __('Not registered yet?') }}
            </a>

            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/auth/login.blade.php
git commit -m "- added registration link to login form"
```

### Task 3: Update README.md with Email Configuration

**Files:**
- Modify: `README.md`

- [ ] **Step 1: Add Email Configuration Section**

In `README.md`, find the `### Environment Variables (db.env)` section. Right below that table, add a new section for Email Configuration:

```markdown
### Environment Variables (db.env)
| Variable | Description |
|----------|-------------|
| `POSTGRES_USER` | DB user for PostgreSQL |
| `POSTGRES_PASSWORD` | DB password |
| `POSTGRES_DB` | Main database name |
| `ADMIN_NAME` | Name for the auto-created admin |
| `ADMIN_EMAIL` | Email for the admin account |
| `ADMIN_PASSWORD` | Password for the admin account |

### Email Configuration (Password Reset)
Password reset functionality is included by default via Laravel Breeze. However, `MAIL_MAILER` in `.env.example` is set to `log` for local development, which writes emails to `storage/logs/laravel.log` rather than sending them.
To enable real email delivery for password resets, update the `MAIL_*` variables in your `.env` file with proper SMTP credentials:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@example.com"
```
```

- [ ] **Step 2: Commit**

```bash
git add README.md
git commit -m "- added email configuration instructions to readme"
```
