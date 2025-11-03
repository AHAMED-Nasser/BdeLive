# Architecture

## Overview

BDELive follows a clean MVC (Model-View-Controller) architecture pattern with PSR-4 autoloading and namespaces. The project is organized into distinct layers for separation of concerns and maintainability.

## Layers

### Controllers

Controllers handle HTTP requests and coordinate between models and views.

**Location**: `app/modules/controllers/**`

**Namespace Structure**:
- `App\Modules\Controllers\` - Base controllers (`DefaultController`, `AuthenticatedController`, `AdminController`)
- `App\Modules\Controllers\Public\` - Public pages (`HomeController`, `TeamController`, `LegalTermsController`)
- `App\Modules\Controllers\Users\` - User-related (`LoginController`, `RegisterController`, `ProfileController`)
- `App\Modules\Controllers\Events\` - Event management (`EventController`, `CreateEventController`)
- `App\Modules\Controllers\Pwd\` - Password reset (`ForgotPasswordController`, `ResetPasswordController`)
- `App\Modules\Controllers\Cookie\` - Cookie consent (`CookieConsentController`)

**Base Controllers**:
- `DefaultController`: Base controller with `render()` method for view rendering
- `AuthenticatedController`: Extends `DefaultController`, enforces user authentication
- `AdminController`: Extends `DefaultController`, enforces admin privileges

**Example**:
```php
namespace App\Modules\Controllers\Users;

use App\Modules\Controllers\AuthenticatedController;

class ProfileController extends AuthenticatedController
{
    public function __construct()
    {
        parent::__construct();
        $this->render('users/profilePageView');
    }
}
```

### Views

Views contain the presentation layer (HTML/PHP templates).

**Location**: `app/modules/views/**`

**Structure**:
- `public/` - Public pages (home, team, legal terms)
- `users/` - User-related pages (login, register, profile)
- `events/` - Event pages (event list, create event)
- `pwd/` - Password reset pages
- `shared/` - Shared components (header, footer, carousel)

**Shared Functions**:
- `start_page(string $title, bool $wouldNav = true)`: Generates HTML header and navigation
- `end_page()`: Generates HTML footer and closing tags

**Example**:
```php
<?php
start_page("Profile - BDELive", true);
?>
<div class="content">
    <!-- View content -->
</div>
<?php end_page(); ?>
```

### Models

Models handle business logic and data manipulation.

**Location**: `app/modules/models/**`

**Namespace Structure**:
- `App\Modules\Models\Users\` - User management (`UserManager`)
- `App\Modules\Models\Admin\` - Admin operations (`EventCreationModel`)
- `App\Modules\Models\Pwd\` - Password reset (`PasswordReset`)

**Example**:
```php
namespace App\Modules\Models\Users;

use App\Core\Database;

class UserManager
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function createUser(string $email, string $password, ...): bool
    {
        // User creation logic
    }
}
```

### Repositories

Repositories handle data access layer operations (CRUD).

**Location**: `app/modules/repositories/**`

**Namespace**: `App\Modules\Repositories\`

**Classes**:
- `EventRepository`: Manages event data access
- `EventRegistrationRepository`: Manages event registration data

**Example**:
```php
namespace App\Modules\Repositories;

use App\Core\Database;

class EventRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getPaginated(int $offset, int $limit): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM EVENTS LIMIT ?, ?');
        $stmt->execute([$offset, $limit]);
        return $stmt->fetchAll();
    }
}
```

### Helpers

Helper classes provide utility functions.

**Location**: `app/modules/helpers/**`

**Namespace**: `App\Modules\Helpers\`

**Classes**:
- `Pagination`: Handles pagination calculations and limits

**Example**:
```php
namespace App\Modules\Helpers;

class Pagination
{
    public function __construct(int $totalItems, int $itemsPerPage = 10, ?int $currentPage = null)
    {
        // Pagination logic
    }

    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }
}
```

### Core

Core classes provide fundamental functionality.

**Location**: `app/core/**`

**Namespace**: `App\Core\`

**Classes**:
- `Database`: PDO singleton for database connections

**Example**:
```php
namespace App\Core;

class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
```

## Routing

The router (`app/rooter.php`) maps URL query parameters to controllers using a naming convention.

### Routing Convention

1. Extract `page` parameter from URL: `?page=legal-terms`
2. Convert to StudlyCase: `LegalTerms`
3. Append `Controller`: `LegalTermsController`
4. Search in namespaces:
   - `App\Modules\Controllers\`
   - `App\Modules\Controllers\Public\`
   - `App\Modules\Controllers\Users\`
   - `App\Modules\Controllers\Events\`
   - `App\Modules\Controllers\Pwd\`
   - `App\Modules\Controllers\Cookie\`

### Example

- `?page=home` → `App\Modules\Controllers\Public\HomeController`
- `?page=login` → `App\Modules\Controllers\Users\LoginController`
- `?page=event` → `App\Modules\Controllers\Events\EventController`
- `?page=forgot-password` → `App\Modules\Controllers\Pwd\ForgotPasswordController`

### Router Code

```php
$page = $_GET['page'] ?? 'home';
$shortName = $toStudlyCase($page) . 'Controller';

$namespaces = [
    'App\\Modules\\Controllers\\',
    'App\\Modules\\Controllers\\Public\\',
    'App\\Modules\\Controllers\\Users\\',
    // ... other namespaces
];

foreach ($namespaces as $ns) {
    $fqcn = $ns . $shortName;
    if (class_exists($fqcn)) {
        new $fqcn();
        break;
    }
}
```

## Rendering

### View Rendering

Controllers use the `render()` method from `DefaultController` to load views:

```php
// In a controller
$this->render('users/profilePageView');
// Loads: app/modules/views/users/profilePageView.php
```

### View Functions

**start_page(string $title, bool $wouldNav = true)**
- Generates HTML document start
- Includes CSS files (Bootstrap, Font Awesome, custom styles)
- Renders navigation bar (conditional)
- Opens `<body>` tag

**end_page()**
- Renders footer with links and social media
- Includes JavaScript files
- Closes `</body>` and `</html>` tags

## Database

### Singleton Pattern

The `Database` class uses the singleton pattern to ensure a single database connection throughout the application lifecycle.

```php
$db = Database::getInstance();
$pdo = $db->getConnection();
```

### Best Practices

- Always use prepared statements: `$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?')`
- Bind parameters: `$stmt->execute([$id])`
- Use PDO exception mode: `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`
- Use associative arrays: `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`

### Example

```php
$pdo = Database::getInstance()->getConnection();
$stmt = $pdo->prepare('SELECT * FROM EVENTS WHERE event_id = ?');
$stmt->execute([$eventId]);
$event = $stmt->fetch();
```

## Autoloading

### PSR-4 Autoloading

The project uses PSR-4 autoloading via Composer:

```json
{
  "autoload": {
    "psr-4": {
      "App\\": "app/",
      "App\\Tests\\": "tests/"
    }
  }
}
```

### Custom Autoloader

A custom autoloader (`app/include/autoload.php`) handles namespace-to-file mapping with the following logic:

1. **Namespaced Classes (PSR-4)**: 
   - Checks if class name starts with `App\`
   - Removes `App\` prefix
   - Converts namespace separators to directory separators
   - Converts directory names to lowercase (preserves class name case)
   - Example: `App\Modules\Controllers\Public\HomeController` → `app/modules/controllers/public/HomeController.php`

2. **Legacy Classes (Backward Compatibility)**:
   - Falls back to legacy search paths if namespaced class not found
   - Searches in predefined directories (models, controllers, helpers, repositories, core, config)

### Namespace Mapping Examples

- `App\Core\Database` → `app/core/Database.php`
- `App\Config\Mailer` → `app/config/Mailer.php`
- `App\Modules\Controllers\Users\LoginController` → `app/modules/controllers/users/LoginController.php`
- `App\Modules\Controllers\Public\HomeController` → `app/modules/controllers/public/HomeController.php`
- `App\Modules\Repositories\EventRepository` → `app/modules/repositories/EventRepository.php`
- `App\Modules\Helpers\Pagination` → `app/modules/helpers/Pagination.php`
- `App\Tests\Unit\Core\DatabaseTest` → `tests/Unit/Core/DatabaseTest.php`

### Class Aliases

For backward compatibility, class aliases are provided for core classes:

```php
// In Database.php
\class_alias(__NAMESPACE__ . '\\Database', 'Database');

// Allows both usages:
use App\Core\Database;  // Recommended
new Database();         // Legacy (still works)
```

This ensures that legacy code using non-namespaced class names continues to work while new code can use proper namespaces.

## Security

### Authentication

**requireLogin()** function (in `app/include/auth.php`):
- Checks if session is active
- Verifies `$_SESSION['user_id']` exists
- Redirects to login page if not authenticated

**requireAdmin()** function:
- Checks user authentication
- Verifies `$_SESSION['user_status'] === 'BDE'`
- Returns 403 if not admin

### CSRF Protection

All forms are protected with CSRF tokens:

1. **Generate Token**: `generateCsrfToken()` creates a unique token
2. **Store in Session**: Token is stored in `$_SESSION['csrf_token']`
3. **Include in Form**: `csrfField()` generates hidden input
4. **Validate**: `validateCsrfToken($token)` verifies token on submission

**Token Expiration**: Tokens expire after 1 hour

### Protected Controllers

- `AuthenticatedController`: Extends `DefaultController`, calls `requireLogin()` in constructor
- `AdminController`: Extends `DefaultController`, calls `requireAdmin()` in constructor

## Testing

### Test Structure

Tests are organized in `tests/Unit/` mirroring the application structure:

```
tests/
└── Unit/
    ├── Core/
    │   └── DatabaseTest.php          # Tests singleton pattern, connection, cloning prevention
    ├── Helpers/
    │   └── PaginationTest.php        # Tests pagination calculations, offsets, limits
    ├── Models/
    │   └── Users/
    │       └── UserManagerTest.php   # Tests password hashing, user CRUD operations
    ├── Repositories/
    │   └── EventRepositoryTest.php   # Tests event data access, pagination
    └── Functions/
        ├── CsrfFunctionsTest.php     # Tests CSRF token generation, validation, expiration
        └── AuthFunctionsTest.php     # Tests authentication and authorization functions
```

### Running Tests

```bash
# Run all tests
composer test

# Run with verbose output
vendor/bin/phpunit --testdox

# Run with coverage
vendor/bin/phpunit --coverage-html tests/coverage/

# Run specific test suite
vendor/bin/phpunit tests/Unit/Core/
```

### Test Configuration

PHPUnit configuration is in `phpunit.xml.dist`:
- **Bootstrap**: `tests/bootstrap.php` - Loads autoloaders and defines database constants
- **Test Directory**: `tests/Unit/` - All unit tests location
- **Coverage**: Excludes `vendor/`, `assets/`, and `views/` directories
- **Cache**: `.phpunit.cache/` directory for test result caching

### Test Bootstrap

The `tests/bootstrap.php` file:
1. Loads Composer autoloader
2. Loads custom autoloader (`app/include/autoload.php`)
3. Attempts to load `app/config/config.php` for database configuration
4. Defines database constants if not already defined (for test environment)

### Database Testing Strategy

Tests that require database connections follow this pattern:

1. **Try-Catch Pattern**: Tests wrap database operations in try-catch blocks
2. **Graceful Skipping**: If database connection fails, tests are marked as skipped using `markTestSkipped()`
3. **Real Database**: Tests use the actual database from `config.php` if available
4. **Pure Unit Tests**: Password hashing and verification tests don't require database

**Example**:
```php
public function testFindUserByEmail(): void
{
    try {
        $userManager = new UserManager();
        $user = $userManager->findUserByEmail('test@example.com');
        $this->assertIsArray($user);
    } catch (PDOException $e) {
        $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
    }
}
```

### Test Coverage

The test suite covers:
-  Core classes (Database singleton)
-  Helper classes (Pagination)
-  Model methods (UserManager password operations)
-  Repository methods (EventRepository pagination)
-  Global functions (CSRF, authentication)
-  Database-dependent operations (skip if DB unavailable)

### Test Namespaces

Tests use the `App\Tests\` namespace:
- `App\Tests\Unit\Core\DatabaseTest`
- `App\Tests\Unit\Helpers\PaginationTest`
- `App\Tests\Unit\Models\Users\UserManagerTest`

## PHPDoc

### Documentation Standards

All public classes and methods are documented with PHPDoc comments:

```php
/**
 * User Manager Model
 *
 * Handles all user-related database operations including CRUD operations,
 * password hashing and verification, and user search functionality.
 *
 * @package BdeLive\Models
 * @author Mohamed-Amine Boudhib, Thomas Palot
 * @version 1.0.0
 */
class UserManager
{
    /**
     * Hash a password using SHA-1
     *
     * Generates a hashed password of the one that is provided
     *
     * @param string $password The plain text password to hash
     * @return string The hashed password
     */
    public function hashPassword(string $password): string
    {
        // Implementation
    }

    /**
     * Create a new user account
     *
     * Inserts a new user record into the database with the provided information.
     *
     * @param string $email User email address
     * @param string $password Plain text password (will be hashed)
     * @param string $firstName User first name
     * @param string $lastName User last name
     * @param string $status User status (e.g., 'BUT1', 'BUT2', 'BDE')
     * @return bool True if user was created successfully, false otherwise
     * @throws PDOException If database operation fails
     */
    public function createUser(
        string $email,
        string $password,
        string $firstName,
        string $lastName,
        string $status
    ): bool {
        // Implementation
    }
}
```

### PHPDoc Tags

- `@package` - Package name
- `@author` - Author(s)
- `@version` - Version number
- `@param` - Parameter description with type
- `@return` - Return type and description
- `@throws` - Exceptions that may be thrown

## Code Quality

### PHPStan

Static analysis is performed at level 8:

```bash
composer phpstan
```

### PHP CodeSniffer

Code style is enforced with PSR-12 standard:

```bash
composer lint      # Check code style
composer lint:fix  # Auto-fix issues
```

### CI/CD

GitHub Actions workflow runs on every push:
- PHPStan (level 8)
- PHPCS (PSR-12)
- PHPUnit (unit tests)
