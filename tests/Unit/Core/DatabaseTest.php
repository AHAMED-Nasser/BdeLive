<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use App\core\Database;
use PDO;
use ReflectionClass;
use Exception;
use Error;

class DatabaseTest extends TestCase
{
    protected function tearDown(): void
    {
        // Réinitialiser le singleton après chaque test
        $reflection = new ReflectionClass(Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setValue(null, null);
    }

    public function testGetInstanceReturnsSameInstance(): void // verifie si ile existe une seul instance Database
    {
        $instance1 = Database::getInstance();
        $instance2 = Database::getInstance();

        $this->assertSame($instance1, $instance2);
    }

    public function testGetConnectionReturnsPDO(): void // garantit que Database renvoie bien une connexion PDO valide.
    {
        $database = Database::getInstance();
        $connection = $database->getConnection();

        $this->assertInstanceOf(PDO::class, $connection);
    }

    public function testGetConnectionReturnsSamePDOInstance(): void // garantit que la connexion PDO est unique et persistante
    {
        $database = Database::getInstance();
        $conn1 = $database->getConnection();
        $conn2 = $database->getConnection();

        $this->assertSame($conn1, $conn2);
    }

    public function testCloneIsPrevented(): void //Ce test garantit qu’il est impossible de dupliquer l’instance du singleton.
    {
        $database = Database::getInstance();

        $this->expectException(Error::class);
        clone $database;
    }

    public function testWakeupIsPrevented(): void // Ce test grantit qu'on ne peut pas restaurer une nouvelle instance du singleton
    {
        $database = Database::getInstance();

        $this->expectException(Exception::class);
        $database->__wakeup();
    }
}
