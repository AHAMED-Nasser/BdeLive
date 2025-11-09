# Tests Unitaires - BdeLive

## Vue d'ensemble

Ce projet utilise des **tests unitaires purs** avec PHPUnit. Les tests n'ont **aucune dépendance** à une base de données réelle ou à des services externes.

## Principes

### Tests unitaires vs Tests d'intégration

- **Tests unitaires** : testent la logique métier en isolation avec des mocks/stubs (dans `tests/Unit/`)
- **Tests d'intégration** : testent les interactions réelles avec la base de données (à créer dans `tests/Integration/` si nécessaire)

Ce projet suit actuellement une approche **100% tests unitaires** conformément aux recommandations de l'équipe.

## Exécution des tests

```bash
composer test
```

Tous les tests s'exécutent en moins de 10 secondes, sans aucune configuration de base de données.

## Structure des tests

```
tests/
├── bootstrap.php           # Initialisation PHPUnit
└── Unit/
    ├── Core/              # Tests du singleton Database
    ├── Functions/         # Tests des fonctions auth/csrf
    ├── Helpers/           # Tests des helpers (pagination)
    ├── Models/            # Tests des models avec mocks PDO
    ├── Repositories/      # Tests des repositories avec mocks PDO
    └── Services/          # Tests des services (Cloudinary)
```

## Stratégie de mocking

### Mocker PDO et PDOStatement

```php
use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;
use ReflectionClass;

class ExampleTest extends TestCase
{
    private PDO $mockPdo;
    private PDOStatement $mockStmt;

    protected function setUp(): void
    {
        $this->mockPdo = $this->createMock(PDO::class);
        $this->mockStmt = $this->createMock(PDOStatement::class);
        
        // Injecter le mock dans Database singleton
        $mockDatabase = $this->createMock(Database::class);
        $mockDatabase->method('getConnection')->willReturn($this->mockPdo);
        
        $reflection = new ReflectionClass(Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, $mockDatabase);
    }

    public function testExample(): void
    {
        // Simuler le résultat d'une requête
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(['id' => 1, 'name' => 'Test']);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        // Tester votre code
    }
}
```

### Valeurs statiques (fixtures)

Au lieu d'utiliser une vraie base de données, définissez des tableaux PHP :

```php
$mockUser = [
    'user_id' => 1,
    'last_name' => 'Doe',
    'first_name' => 'John',
    'email' => 'john.doe@example.com',
    'password' => '$2y$10$hashedpassword'
];

$this->mockStmt->method('fetch')->willReturn($mockUser);
```

## Avantages de cette approche

- **Performance** : tests 10x plus rapides (< 10s au lieu de > 60s)
- **Portabilité** : aucun setup MySQL requis pour les développeurs
- **Fiabilité** : tests déterministes, pas d'état partagé
- **Isolation** : chaque test est indépendant
- **CI/CD** : workflow GitHub Actions simplifié et rapide

## Outils de qualité

### PHPStan (niveau 8)

Analyse statique stricte du code :

```bash
composer phpstan
```

### PHPCS (PSR-12)

Vérification du style de code :

```bash
composer lint
```

Pour corriger automatiquement :

```bash
composer lint:fix
```

## Conventions

- Tous les tests doivent extends `PHPUnit\Framework\TestCase`
- Utiliser `declare(strict_types=1);` en haut de chaque fichier
- Nommer les méthodes de test : `testXxxxxYyyyyZzzzz()`
- Utiliser `setUp()` pour initialiser les mocks
- Utiliser `tearDown()` pour nettoyer (reset singleton, etc.)

## FAQ

**Q : Pourquoi ne pas utiliser une vraie base de données pour les tests ?**

R : Les vrais tests unitaires doivent tester la logique métier en isolation, sans dépendances externes. Cela les rend plus rapides, plus fiables et plus faciles à maintenir. Les tests avec vraie DB sont des tests d'intégration.

**Q : Comment tester les requêtes SQL complexes ?**

R : Mocquez `PDOStatement` et validez que les bons paramètres sont passés. Pour valider la logique SQL complète, créez des tests d'intégration séparés.

**Q : Les tests passent localement mais échouent en CI/CD ?**

R : Vérifiez que vous n'avez pas de dépendances cachées (fichiers, variables d'environnement). Les tests unitaires doivent être 100% autonomes.

## Commandes utiles

```bash
# Exécuter tous les tests
composer test

# Analyse PHPStan niveau 8
composer phpstan

# Vérifier le style PSR-12
composer lint

# Corriger le style automatiquement
composer lint:fix

# Tout valider en une commande
composer phpstan && composer lint && composer test
```
