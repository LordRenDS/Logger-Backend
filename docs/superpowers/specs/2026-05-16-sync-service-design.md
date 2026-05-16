# Task 4: PC Discovery and Data Sync Service Design

## Goal
Implement a service to discover PCs based on a unique identifier and synchronize activity data (processes and schedules) from the client to the server.

## Architecture
- **PcService**: Responsible for finding or creating PC records.
- **SyncService**: Handles batch insertion of activity data (processes and schedules).
- **SyncController**: Provides API endpoints for the client to send data.
- **Routes**: Secure endpoints under `v1/sync` prefix.

## Data Flow
1. Client sends a request to `/api/v1/sync/processes` or `/api/v1/sync/schedules` with `pc_unique_id`, `pc_name`, and `data`.
2. Controller authenticates the user.
3. Controller uses `PcService` to get the `Pc` model instance.
4. Controller uses `SyncService` to save the data.
5. Controller returns the count of records synced.

## Components

### PcService
- `findOrCreatePc(User $user, string $uniqueId, ?string $name = null): Pc`
  - Search by `unique_id`.
  - Update `last_seen_at`.
  - If not found, create for the user.
  - If found but owner is different? (Assumed: PC belongs to the user who sends it, or we might need to handle ownership). Given the task, we associate it with the provided user.

### SyncService
- `syncProcesses(Pc $pc, array $data): int`
  - Map data to `processes` table format.
  - Batch insert for performance.
- `syncSchedules(Pc $pc, array $data): int`
  - Map data to `schedules` table format.
  - Find `pc_status_id` based on 'on'/'off' strings in data.
  - Batch insert.

### SyncController
- `POST /api/v1/sync/processes`
- `POST /api/v1/sync/schedules`
- Validation of payload.
- Swagger annotations.

## Error Handling
- Validate that `data` is an array and contains required fields.
- Handle database exceptions during batch insert.

## Testing
- Feature tests for both endpoints.
- Mocking services to test controller logic.
- Testing `PcService` and `SyncService` in isolation.
