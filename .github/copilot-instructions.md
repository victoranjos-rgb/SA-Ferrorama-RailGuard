# Copilot instructions for RailGuard

This repository is a PHP + MySQL web application for railway operations. Use the existing architecture and conventions in place under `MAIN/backend/` and `MAIN/frontend/`.

## Project context
- Backend code belongs in `MAIN/backend/`.
- API files live in `MAIN/backend/api/` and should respond with JSON.
- UI pages and client scripts live in `MAIN/frontend/`.
- Tests are PHP scripts in `tests/`.

## Coding expectations
- Keep changes minimal and aligned with current patterns.
- Use prepared statements for all database operations.
- Preserve session-based auth and role checks.
- Do not commit credentials or local configuration files.
- Prefer existing naming, JSON response, and frontend fetch patterns.

## Verification
- Run the relevant PHP tests whenever touching a feature module.
- Validate edited PHP files with `php -l` when practical.
- Check that the feature still works end-to-end from API to UI behavior.

## Security
- Never store secrets in code.
- Keep validation and authorization in place.
- Avoid direct SQL concatenation or unsafe input handling.

## Example commands
- `php tests/test_aprovacao.php`
- `php tests/test_crud_trens.php`
- `php -l MAIN/backend/api/login.php`
