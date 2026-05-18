# Dashboard Improvements Design Specification

## Overview
This document outlines the design for improving the Logger Backend dashboard. The improvements include adding user management capabilities for administrators, separating PC lists from activity logs, and introducing filtering and pagination.

## Architecture

**1. Routing & Controllers**
*   **`DashboardController@index`**: 
    *   **Admin**: Returns a paginated list of all users.
    *   **User**: Returns a paginated list of the user's PCs.
*   **`UserController`**:
    *   `show(User $user)`: Allows an admin to view a specific user's PCs (paginated).
    *   `destroy(User $user)`: Allows an admin to delete a user. The database should cascade deletions to related PCs and activities, or the controller should handle it.
*   **`PcActivityController`**:
    *   `index(Pc $pc)`: Returns a paginated, filterable, and sortable list of activities (processes) for a specific PC. Authorization must ensure only admins or the PC's owner can access this.

**2. State Management**
*   **`per_page` Setting**: The number of records displayed per page will be stored in the user's session (`session(['per_page' => value])`). This setting applies universally to paginated lists (Users, PCs, Activities). The default value is 15.

**3. Views & User Interface**
*   **`resources/views/dashboard.blade.php`**: 
    *   Updated to show a clean list of Users (for admins) or PCs (for users).
    *   Activities table is removed from this view.
    *   Added session-based `per_page` selector dropdown.
    *   Admin view gets a "View PCs" button and a "Delete" button per user.
    *   User view gets a "View Activities" button per PC.
*   **`resources/views/users/show.blade.php`**: 
    *   New view for Admins. Reuses the PC list layout from the user dashboard.
*   **`resources/views/pcs/activities.blade.php`**:
    *   New view for displaying PC activities.
    *   Includes input fields for filtering by `process_name` and `window_name`.
    *   Table headers act as sortable links (toggling ascending/descending order).
    *   Includes pagination links and the `per_page` selector.

## Error Handling & Validation
*   Authorization checks (Policies or inline Gate checks) will throw 403 Forbidden if an unauthorized user attempts to view a PC or another user's dashboard.
*   Deleting a user must be confirmed on the frontend (e.g., via JavaScript confirmation dialogue) to prevent accidental data loss.

## Testing Strategy
*   Add feature tests for the new routes (`UserController`, `PcActivityController`).
*   Verify authorization logic (Admin can view all, User can only view own).
*   Verify pagination and filtering logic in `PcActivityController`.
