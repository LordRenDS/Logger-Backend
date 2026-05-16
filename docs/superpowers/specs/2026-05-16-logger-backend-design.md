# Design Spec: Logger Backend Server

## Overview
A Laravel-based server for recording and analyzing PC activity. It provides a REST API for data synchronization from WinForms clients and a web interface for users and admins.

## Tech Stack
- **Framework:** Laravel 13.x
- **PHP:** 8.4
- **Database:** PostgreSQL 18
- **Authentication:** JWT (via `php-open-source-saver/jwt-auth`)
- **API Documentation:** L5-Swagger (OpenAPI 3.1)
- **Containerization:** Docker Compose (Apache + PostgreSQL 18)
- **Architecture:** Controller -> Service -> Model pattern

## Database Schema (PostgreSQL 18)

### Users
- `id` (BigInt, PK)
- `name` (String)
- `email` (String, Unique)
- `password` (String, Hashed)
- `role` (Enum: 'admin', 'user')
- `timestamps`

### PCs
- `id` (BigInt, PK)
- `user_id` (ForeignId -> users.id)
- `unique_id` (String, Unique) - Hardware ID or unique name from client
- `name` (String, Nullable)
- `last_seen_at` (Timestamp)
- `timestamps`

### Processes (Activity Logs)
- `id` (BigInt, PK)
- `pc_id` (ForeignId -> pcs.id)
- `process_start` (Timestamp)
- `process_name` (String)
- `window_name` (String)
- `duration` (Integer, seconds)
- `timestamps`

### Schedules (PC Status)
- `id` (BigInt, PK)
- `pc_id` (ForeignId -> pcs.id)
- `timestamp` (Timestamp)
- `pc_status_id` (ForeignId -> pc_statuses.id)
- `timestamps`

### PcStatuses
- `id` (BigInt, PK)
- `status` (String: 'on', 'off')

## API Specification (v1)

### Authentication
- `POST /api/v1/auth/login` -> Returns JWT access token.
- `POST /api/v1/auth/register` -> Creates new user account.
- `POST /api/v1/auth/logout` -> Invalidates token.
- `GET /api/v1/auth/me` -> Current user info and role.

### Data Synchronization (Batch)
- `POST /api/v1/sync/processes`
  - Payload: `{ "pc_unique_id": "...", "data": [...] }`
  - Logic: Auto-creates PC entry if `pc_unique_id` is new for the user.
- `POST /api/v1/sync/schedules`
  - Payload: `{ "pc_unique_id": "...", "data": [...] }`

### Management
- `GET /api/v1/pc/list` -> List of PCs owned by the user.

## Web Interface (Blade)
- **Guest:** Welcome, Login, Register pages.
- **User Dashboard:**
  - View activity logs filtered by PC.
  - View PC status history.
- **Admin Panel:**
  - View all registered users.
  - Access logs of any user/PC.

## Docker Infrastructure
- `app`: PHP 8.4 Apache container.
- `db`: PostgreSQL 18 container.
- `adminer`: Database management tool.
- Persistence via Docker volumes for PG data and storage logs.

## Security & Validation
- Middleware `auth:api` (JWT) for all sync and personal data endpoints.
- Request validation for all incoming batch data.
- User-PC affiliation check in services.
