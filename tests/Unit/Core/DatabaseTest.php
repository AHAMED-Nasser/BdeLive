<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use PDO;
use ReflectionClass;
use Exception;
use Error;

/**
 * Tests unitaires pour la classe Database (singleton de connexion PDO).
 */
class DatabaseTest extends TestCase
{
    /**
     * Réinitialise le singleton après chaque test.
     */
    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setValue(null, null);
    }

    /**
     * Vérifie que getInstance() retourne toujours la même instance.
     */
    public function testGetInstanceReturnsSameInstance(): void
    {
        $instance1 = Database::getInstance();
        $instance2 = Database::getInstance();

        $this->assertSame($instance1, $instance2);
    }

    /**
     * Vérifie que getConnection() retourne bien un objet PDO.
     */
    public function testGetConnectionReturnsPDO(): void
    {
        $database = Database::getInstance();
        $connection = $database->getConnection();

        $this->assertInstanceOf(PDO::class, $connection);
    }

    /**
     * Vérifie que getConnection() retourne toujours la même instance PDO.
     */
    public function testGetConnectionReturnsSamePDOInstance(): void
    {
        $database = Database::getInstance();
        $conn1 = $database->getConnection();
        $conn2 = $database->getConnection();

        $this->assertSame($conn1, $conn2);
    }

    /**
     * Vérifie que le clonage de l'instance Database est interdit.
     */
    public function testCloneIsPrevented(): void
    {
        $database = Database::getInstance();

        $this->expectException(Error::class);
        clone $database;
    }

    /**
     * Vérifie que la désérialisation (wakeup) est interdite.
     */
    public function testWakeupIsPrevented(): void
    {
        $database = Database::getInstance();

        $this->expectException(Exception::class);
        $database->__wakeup();
    }
}
