# APPLICATION
A client-server application that records activity on the user's PC and allows you to analyze what the user was doing and when.
The client-side component is a WinForms application that typically runs in the background and periodically determines which process is currently active and the title of its window. It also records when the PC is turned on or off. The application window (usually hidden in the system tray) includes a DataGridView displaying the information collected so far. When the currently active process changes (after all, we don’t switch between windows every second), a set of data (date, time, process name, window title) is recorded locally (in the application’s own database, using SQLite) and sent to the server.
The server-side component is a Laravel-based REST API and web dashboard designed to handle multi-user activity tracking across numerous devices. It serves as a centralized repository that receives, validates, and stores data from WinForms client instances. The server employs an optimized synchronization service that processes batch uploads of activity logs (including process names, window titles, and durations) and PC power state history (on/off events). Built with a Controller-Service-Model architecture, it ensures a clean separation of concerns and scalability. Security is enforced through JWT-based authentication for the API and session-based authentication for the web dashboard, where users can monitor their PCs' real-time status and browse historical data. The system also includes automated device discovery, mapping each PC to its owner, and comprehensive OpenAPI documentation for easy client integration.
The server is built using **PHP 8.4 (Laravel 12)** and **PostgreSQL 18**.
**Current Server State:**
- **Security**: JWT-based authentication for API and session-based for the dashboard.
- **Architecture**: Controller-Service-Model pattern for clean business logic separation.
- **Data Sync**: Optimized batch ingestion for PC activity logs (processes and status changes) with device ownership validation.
- **PC Management**: Auto-discovery of new devices and tracking of online/offline status via `last_seen_at`.
- **Monitoring**: Blade-based dashboard for real-time activity overview and historical analysis.
- **Documentation**: Built-in OpenAPI 3.1 (Swagger) documentation for API integration.
- **Deployment**: Fully dockerized environment (Apache, PHP-FPM, PostgreSQL). Requires `db.env` for database and admin account auto-creation.
# COMMIT MESSAGE
Write commits only as a list with a '-' separator at the beginning, each change on a new line and in lowercase.
Do not write changes in each file if you can logically group changes from several files. Use the English language.