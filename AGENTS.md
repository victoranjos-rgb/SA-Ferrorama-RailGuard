# AGENTS.md

## Project overview
This repository contains the RailGuard web application for railway operations, including authentication, maintenance workflows, route monitoring, train management, and reporting dashboards.

The app is structured as follows:
- `MAIN/backend/` — PHP APIs, DB access, and import scripts
- `MAIN/frontend/` — HTML, CSS, and JavaScript screens
- `tests/` — PHP regression tests for the main modules

## Stack and constraints
- Backend: PHP with MySQL via `mysqli`
- Frontend: HTML, CSS, JavaScript
- Session/auth: PHP sessions, `password_hash()`, `password_verify()`, JSON API responses
- Database config is expected in `MAIN/backend/config.php` and should not be committed to version control
- Keep the project compatible with XAMPP local development and the Aiven-hosted production database

## Operating rules for agents
- Prefer small, surgical changes that match the existing project style.
- Preserve the current frontend/backend separation.
- Use prepared statements for all database queries.
- Validate input before writing to the database or returning data.
- Return JSON responses in the existing API format used in `MAIN/backend/api/`.
- Do not expose sensitive credentials, connection strings, or secrets in code or logs.
- Do not add ad hoc database migrations without updating the relevant SQL or scripts.
- When changing a flow that affects auth, make sure the session and role checks remain consistent.

## Project conventions
- Database setup and seed scripts live under `MAIN/backend/`.
- API endpoints belong in `MAIN/backend/api/` and should follow the established JSON pattern.
- Frontend screens live under `MAIN/frontend/` and usually call backend APIs via `fetch()`.
- Tests are PHP scripts under `tests/` and should validate the behavior they cover without leaving leftover data.

## Validation steps
Before finalizing work:
1. Run the most relevant PHP test file for the changed module, if available.
2. Check PHP syntax for edited scripts with `php -l`.
3. Verify the affected frontend/backend flow still matches the expected JSON and redirect behavior.

Examples:
- `php tests/test_aprovacao.php`
- `php tests/test_crud_trens.php`
- `php tests/test_dashboard.php`
- `php -l MAIN/backend/api/login.php`

## Critical reminders
- Keep `config.php` local and ignored.
- Do not bypass approval and authorization checks for manager-only flows.
- Continue using UTF-8 and `utf8mb4` conventions for database and server-side response handling.
- When implementing new screens or APIs, respect existing naming conventions and keep them aligned with the service structure already used in the repo.
