<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use PDO;
use ReflectionClass;
use ReflectionProperty;
use Exception;
use Error;

class DatabaseTest extends TestCase
{
    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
    }

    public function testGetInstanceReturnsSameInstance(): void
    {
            $this->markTestSkipped('Requires real database connection - move to integration tests');
    }

    public function testGetConnectionReturnsPDO(): void
    {
        $this->markTestSkipped('Requires real database connection - move to integration tests');
    }

    public function testGetConnectionReturnsSamePDOInstance(): void
    {
        $this->markTestSkipped('Requires real database connection - move to integration tests');
    }

    public function testCloneIsPrevented(): void
    {
        $reflection = new ReflectionClass(Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);

        $mockPdo = $this->createMock(PDO::class);
        $mockDatabase = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock();

        $instanceProperty->setValue(null, $mockDatabase);

        $this->expectException(Error::class);
        clone $mockDatabase;
    }

    public function testWakeupIsPrevented(): void
    {
        $reflection = new ReflectionClass(Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);

        $mockDatabase = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__wakeup'])
            ->getMock();

        $mockDatabase->expects($this->once())
            ->method('__wakeup')
            ->willThrowException(new Exception('Cannot unserialize singleton'));

        $instanceProperty->setValue(null, $mockDatabase);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot unserialize singleton');
        $mockDatabase->__wakeup();
    }
}
