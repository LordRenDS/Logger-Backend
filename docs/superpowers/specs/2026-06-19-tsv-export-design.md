# TSV Export Design

## Purpose
Add the ability to export all PC activity data and PC status data to a `.tsv` format from the dashboard.

## Architecture & Data Flow
1. **Routes**:
   - `GET /pcs/{pc}/activities/export` (named `pcs.activities.export`)
   - `GET /pcs/{pc}/statuses/export` (named `pcs.statuses.export`)
2. **Controllers**:
   - Add `export` method to `PcActivityController`. It will use `cursor()` to stream all processes for the PC to a TSV file using `StreamedResponse` to handle large amounts of data efficiently.
   - Add `export` method to `PcStatusController`. It will use `cursor()` to stream all statuses for the PC to a TSV file.
   - Ensure the authorization logic (`auth()->user()->role !== 'admin' && auth()->id() !== $pc->user_id`) is applied before exporting.
3. **Data Format**:
   - TSV (Tab-Separated Values).
   - Activities columns: `Start Time`, `Process`, `Window Title`, `Duration (HH:MM:SS)`
   - Statuses columns: `Timestamp`, `Status`
4. **UI**:
   - Add "Export to TSV" button to `resources/views/pcs/activities.blade.php`.
   - Add "Export to TSV" button to `resources/views/pcs/statuses.blade.php`.
   - Buttons will be styled to match the interface and placed prominently (e.g. next to the "Back" or "Filters" area).

## Testing & Verification
- Verify the exported file is actually TSV (tabs instead of commas).
- Verify memory limits are respected using `cursor()` on large tables.
- Verify access control (unauthorized users cannot export data they don't own).
