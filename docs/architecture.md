# Architecture

## Layers
- Controllers: `app/modules/controllers/**`
- Views: `app/modules/views/**`
- Models: `app/modules/models/**`
- Repositories: `app/modules/repositories/**`
- Core: `app/core/**` (PDO singleton, config)

## Routing
`app/rooter.php` maps `?page=` to a controller name using StudlyCase + `Controller`.
Example: `?page=legal-terms` → `LegalTermsController`.

## Rendering
`DefaultController::render(string $viewPath)` requires views relative to `modules/views`.
Controllers either extend `DefaultController` or `AuthenticatedController` for login-protected pages.

## Database
`Database` is a singleton that returns a shared `PDO` via `getConnection()`.
Always use prepared statements with `PDO::prepare()` and bound parameters.

## Security
- Auth-only pages extend `AuthenticatedController` which enforces `requireLogin()`.
- CSRF tokens are validated centrally in form flows.


