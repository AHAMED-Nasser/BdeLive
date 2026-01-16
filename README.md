# BDELIVE

## Description

BDELive is the official website of the Student Office (Bureau Des Étudiants) for the Computer Science Bachelor's program at Aix-en-Provence. The platform allows students to stay informed about the latest news and events organized by the BDE.

## Features

BDELive provides the following functionalities:

- **User Account Management**: Create an account, login, and manage your profile
- **Password Reset**: Reset your password via email using secure tokens
- **Event Management**: View upcoming events, register for events, and create events (for BDE members)
- **Team Information**: Learn about the BDE team members and their roles
- **Legal Information**: Access legal terms and site map

## Live Website

**Production URL**: [bdelivesae.alwaysdata.net](https://bdelivesae.alwaysdata.net)

## Requirements

- **PHP**: ^8.2
- **Database**: MySQL/MariaDB
- **Web Server**: Apache or Nginx
- **Composer**: For dependency management

## Installation

### 1. Clone the Repository

```bash
  git clone <repository-url>
  cd BdeLive
```

### 2. Install PHP Extensions

Install required PHP extensions:

**On Debian/Ubuntu:**
```bash
sudo apt-get install php-mbstring php-xml php-curl php-mysql
```

**On Fedora/RHEL:**
```bash
sudo dnf install php-mbstring php-xml php-curl php-mysqlnd
```

**Verify installation:**
```bash
php -m | grep -E "(mbstring|xml|curl|pdo_mysql)"
```

### 3. Install Composer Dependencies

```bash
# Install all dependencies (including dev dependencies)
composer install

# For production, install without dev dependencies
composer install --no-dev --optimize-autoloader
```

**Important**: The `vendor/` directory is not in Git (it's in `.gitignore`). You must run `composer install` on the server after deployment.

See `DEPLOYMENT.md` for detailed deployment instructions.

### 4. Configure Database

1. Copy `app/config/config.php.example` to `app/config/config.php` (if exists)
2. Update database credentials in `app/config/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'your_database');
   define('DB_USER', 'your_username');
   define('DB_PASSWORD', 'your_password');
   define('DB_CHARSET', 'utf8mb4');
   ```

### 5. Setup Database Schema

Import the database schema from `BDELive_database_1.1.sql` into your MySQL database.

### 6. Configure Web Server

Point your web server document root to the `app/` directory.

**Example Apache configuration:**
```apache
<VirtualHost *:80>
    ServerName bdelive.local
    DocumentRoot /path/to/BdeLive/app
    <Directory /path/to/BdeLive/app>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 7. Generate index.html documentation

For generate the documentation, you have to install composer, with the following commande

```bash
  composer install
```
After that :
- ```phpDocumentor.phar``` file will appear in your project root.
- ```docs/api``` folder will be created, and you'll find ```index.html```.


## Project Structure

The project follows a clean MVC architecture with PSR-4 namespaces:

```
BdeLive/
│
├── app/
│   ├── assets/          # Static assets (CSS, JS, images)
│   ├── Config/          # Configuration files
│   ├── ├── cloudinary.php # Cloudinary service configuration
│   │   ├── config.php   # Database and SMTP configuration
│   │   └── Mailer.php   # Email service
│   ├── core/            # Core classes
│   │   └── Database.php # PDO singleton
│   ├── include/         # Shared functions
│   │   ├── auth.php     # Authentication helpers
│   │   ├── autoload.php # Custom autoloader
│   │   └── csrf.php     # CSRF protection
│   ├── modules/
│   │   ├── controllers/ # Controllers (MVC)
│   │   │   ├── public/  # Public controllers
│   │   │   ├── users/   # User-related controllers
│   │   │   ├── events/  # Event controllers
│   │   │   └── pwd/     # Password reset controllers
│   │   ├── models/      # Data models
│   │   ├── repositories/# Data access layer
│   │   ├── helpers/     # Utility classes
│   │   └── views/       # View templates
│   ├── cron/            # Cron jobs
│   ├── index.php        # Application entry point
│   └── rooter.php       # Router
│
├── tests/               # Unit tests (PHPUnit)
│   └── Unit/
│
├── vendor/              # Composer dependencies
├── composer.json        # Composer configuration
├── phpunit.xml.dist     # PHPUnit configuration
├── phpstan.neon         # PHPStan configuration
└── phpcs.xml.dist       # PHPCS configuration
```

## Architecture

### MVC Pattern

The project follows the Model-View-Controller (MVC) architectural pattern:

- **Models**: Data models in `app/modules/models/`
- **Views**: Presentation layer in `app/modules/views/`
- **Controllers**: Request handlers in `app/modules/controllers/`

### PSR-4 Autoloading

All classes use PSR-4 namespaces for better code organization and autoloading:

**Namespace Mapping**:
- `App\Core\` → `app/core/`
- `App\Config\` → `app/config/`
- `App\Modules\Controllers\` → `app/modules/controllers/`
- `App\Modules\Controllers\Public\` → `app/modules/controllers/public/`
- `App\Modules\Controllers\Users\` → `app/modules/controllers/users/`
- `App\Modules\Controllers\Events\` → `app/modules/controllers/events/`
- `App\Modules\Controllers\Pwd\` → `app/modules/controllers/pwd/`
- `App\Modules\Models\Users\` → `app/modules/models/users/`
- `App\Modules\Models\Admin\` → `app/modules/models/admin/`
- `App\Modules\Models\Pwd\` → `app/modules/models/pwd/`
- `App\Modules\Repositories\` → `app/modules/repositories/`
- `App\Modules\Helpers\` → `app/modules/helpers/`
- `App\Tests\` → `tests/`

**Autoloading**:
- Composer PSR-4 autoloader handles namespaced classes
- Custom autoloader (`app/include/autoload.php`) provides backward compatibility for legacy classes
- Class aliases are maintained for backward compatibility (e.g., `Database` class can be used without namespace)

**Example Usage**:
```php
use App\Core\Database;
use App\Modules\Models\Users\UserManager;
use App\Modules\Repositories\EventRepository;

$db = Database::getInstance();
$userManager = new UserManager();
$eventRepo = new EventRepository();
```

### Routing

The router (`app/rooter.php`) maps URL parameters to controllers:
- `?page=home` → `App\Modules\Controllers\Public\HomeController`
- `?page=login` → `App\Modules\Controllers\Users\LoginController`
- `?page=event` → `App\Modules\Controllers\Events\EventController`

## Testing

### Running Tests

The project uses PHPUnit for unit testing:

```bash
# Run all tests
composer test

# Run tests with verbose output
vendor/bin/phpunit --testdox

# Run tests with coverage
vendor/bin/phpunit --coverage-html tests/coverage/

# Run specific test suite
vendor/bin/phpunit tests/Unit/Core/
```

### Test Structure

Tests are located in `tests/Unit/` and mirror the application structure:

```
tests/
└── Unit/
    ├── Core/
    │   └── DatabaseTest.php          # Database singleton tests
    ├── Helpers/
    │   └── PaginationTest.php        # Pagination helper tests
    ├── Models/
    │   └── Users/
    │       └── UserManagerTest.php   # User model tests
    ├── Repositories/
    │   └── EventRepositoryTest.php   # Event repository tests
    └── Functions/
        ├── CsrfFunctionsTest.php     # CSRF protection tests
        └── AuthFunctionsTest.php     # Authentication tests
```

### Test Configuration

The test suite is configured in `phpunit.xml.dist`:
- **Bootstrap**: `tests/bootstrap.php` - Loads autoloaders and defines test constants
- **Test Directory**: `tests/Unit/` - All unit tests location
- **Coverage**: Excludes vendor, assets, and view directories

### Database Tests

Tests that require database connections are configured to:
- Use the database from `app/config/config.php` if available
- Skip gracefully if database connection is not available (using `markTestSkipped()`)
- Test password hashing operations without database (pure unit tests)

**Example**:
```php
public function testFindUserByEmail(): void
{
    try {
        $userManager = new UserManager();
        $user = $userManager->findUserByEmail('test@example.com');
        $this->assertIsArray($user);
    } catch (PDOException $e) {
        $this->markTestSkipped('Database connection not available');
    }
}
```

## Code Quality

### PHPStan

Static analysis is performed using PHPStan at level 8:

```bash
composer phpstan
```
Static Code Analysis with PHPStan :

```bash
vendor/bin/phpstan analyse --level 8 app

### PHP CodeSniffer

Code style is enforced using PHPCS with PSR-12 standard:

```bash
# Check code style
composer lint

# Auto-fix code style issues
composer lint:fix
```

## CI/CD

The project uses GitHub Actions for continuous integration. The workflow (`.github/workflows/phpstan.yml`) automatically runs:

- **PHPStan**: Static analysis at level 8
- **PHPCS**: Code style validation (PSR-12)
- **PHPUnit**: Unit tests execution

All checks run on every push and pull request.

## Security

### Session Configuration

The project implements secure PHP session cookie configuration following OWASP best practices:

- **httponly**: Session cookies are not accessible via JavaScript (XSS protection)
- **secure**: Cookies are only transmitted over HTTPS connections
- **samesite**: Protection against CSRF attacks
- **Lifespan**: 30 minutes to limit exposure in case of compromise

This configuration is defined in `app/index.php` and applies site-wide.

### CSRF Protection

All forms are protected against Cross-Site Request Forgery attacks:

- **Token Generation**: Each form receives a unique, random token
- **Server-side Validation**: All controllers verify token validity before processing
- **Expiration**: Tokens expire after 1 hour to limit risks
- **Protected Forms**: Login, Register, Forgot Password, Reset Password, Verify Token, Create Event

### HTTP Security Headers

The site sends security headers to strengthen protection:

- **X-Frame-Options**: Anti-clickjacking protection
- **Strict-Transport-Security**: Forces HTTPS usage
- **X-Content-Type-Options**: Prevents MIME-sniffing
- **X-XSS-Protection**: Protection against XSS attacks
- **Referrer-Policy**: Controls referrer information

### OWASP Compliance

The project addresses several OWASP Top 10 2021 categories:

- **A01:2021 - Broken Access Control**: Session and CSRF protection
- **A03:2021 - Injection**: Protection against cookie theft via XSS and security headers
- **A05:2021 - Security Misconfiguration**: Correct cookie and HTTP headers configuration
- **A07:2021 - Identification and Authentication Failures**: Secure session tokens

## Database Schema

The database schema is represented by the following diagram:

<img width="892" height="340" alt="Database Schema" src="https://github.com/user-attachments/assets/4c6c2363-da5c-47d6-99f5-854286a32db4" />

## Hosting and Database

The site is hosted on **AlwaysData**, and the database is managed via **phpMyAdmin**.

## Programming Languages

- **PHP** (^8.2)
- **SQL** (MySQL/MariaDB)
- **HTML / CSS**
- **JavaScript**

## Authors

- AHAMED Nasser
- BOUDHIB Mohamed-Amine
- CANTOR Romain
- CHETIOUI Willem
- HELALI Amin
- PALOT Thomas

## License

Academic project - Educational use only

