<?php

namespace Tests\Unit;

use Concept\DBAL\DbalManager;
use Concept\DBAL\DbalManagerInterface;
use Concept\DBAL\DML\DmlManagerInterface;
use Concept\DBAL\DDL\DdlManagerInterface;
use PHPUnit\Framework\TestCase;

class DbalManagerTest extends TestCase
{
    private DbalManagerInterface $dbalManager;
    private DmlManagerInterface $dmlManager;
    private DdlManagerInterface $ddlManager;

    protected function setUp(): void
    {
        $this->dmlManager = $this->createMock(DmlManagerInterface::class);
        $this->ddlManager = $this->createMock(DdlManagerInterface::class);

        $this->dbalManager = new DbalManager($this->dmlManager, $this->ddlManager);
    }

    public function testDmlReturnsDmlManager(): void
    {
        $result = $this->dbalManager->dml();

        $this->assertSame($this->dmlManager, $result);
        $this->assertInstanceOf(DmlManagerInterface::class, $result);
    }

    public function testDdlReturnsDdlManager(): void
    {
        $result = $this->dbalManager->ddl();

        $this->assertSame($this->ddlManager, $result);
        $this->assertInstanceOf(DdlManagerInterface::class, $result);
    }

    public function testDmlAlwaysReturnsSameInstance(): void
    {
        $result1 = $this->dbalManager->dml();
        $result2 = $this->dbalManager->dml();

        $this->assertSame($result1, $result2);
    }

    public function testDdlAlwaysReturnsSameInstance(): void
    {
        $result1 = $this->dbalManager->ddl();
        $result2 = $this->dbalManager->ddl();

        $this->assertSame($result1, $result2);
    }
}
