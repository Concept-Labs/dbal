<?php

namespace Tests\Unit\DDL;

use Concept\DBAL\DDL\DdlManager;
use Concept\DBAL\DDL\DdlManagerInterface;
use Concept\DBAL\DDL\Builder\CreateTableBuilderInterface;
use Concept\DBAL\DDL\Builder\AlterTableBuilderInterface;
use Concept\DBAL\DDL\Builder\DropTableBuilderInterface;
use Concept\DBAL\DDL\Builder\TruncateTableBuilderInterface;
use Concept\DBAL\DDL\Builder\Factory\CreateTableBuilderFactoryInterface;
use Concept\DBAL\DDL\Builder\Factory\AlterTableBuilderFactoryInterface;
use Concept\DBAL\DDL\Builder\Factory\DropTableBuilderFactoryInterface;
use Concept\DBAL\DDL\Builder\Factory\TruncateTableBuilderFactoryInterface;
use Concept\DBAL\Expression\SqlExpressionInterface;
use Concept\DBC\ConnectionInterface;
use PHPUnit\Framework\TestCase;

class DdlManagerTest extends TestCase
{
    private DdlManagerInterface $ddlManager;
    private ConnectionInterface $connection;
    private SqlExpressionInterface $expression;
    private CreateTableBuilderFactoryInterface $createTableFactory;
    private AlterTableBuilderFactoryInterface $alterTableFactory;
    private DropTableBuilderFactoryInterface $dropTableFactory;
    private TruncateTableBuilderFactoryInterface $truncateTableFactory;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(ConnectionInterface::class);
        $this->expression = $this->createMock(SqlExpressionInterface::class);
        $this->createTableFactory = $this->createMock(CreateTableBuilderFactoryInterface::class);
        $this->alterTableFactory = $this->createMock(AlterTableBuilderFactoryInterface::class);
        $this->dropTableFactory = $this->createMock(DropTableBuilderFactoryInterface::class);
        $this->truncateTableFactory = $this->createMock(TruncateTableBuilderFactoryInterface::class);

        $this->ddlManager = new DdlManager(
            $this->connection,
            $this->expression,
            $this->createTableFactory,
            $this->alterTableFactory,
            $this->dropTableFactory,
            $this->truncateTableFactory
        );
    }

    public function testCreateTableReturnsCreateTableBuilder(): void
    {
        $createTableBuilder = $this->createMock(CreateTableBuilderInterface::class);
        $createTableBuilder->expects($this->once())
            ->method('createTable')
            ->with('users')
            ->willReturn($createTableBuilder);

        $this->createTableFactory->expects($this->once())
            ->method('create')
            ->willReturn($createTableBuilder);

        $result = $this->ddlManager->createTable('users');

        $this->assertInstanceOf(CreateTableBuilderInterface::class, $result);
    }

    public function testAlterTableReturnsAlterTableBuilder(): void
    {
        $alterTableBuilder = $this->createMock(AlterTableBuilderInterface::class);
        $alterTableBuilder->expects($this->once())
            ->method('alterTable')
            ->with('users')
            ->willReturn($alterTableBuilder);

        $this->alterTableFactory->expects($this->once())
            ->method('create')
            ->willReturn($alterTableBuilder);

        $result = $this->ddlManager->alterTable('users');

        $this->assertInstanceOf(AlterTableBuilderInterface::class, $result);
    }

    public function testDropTableReturnsDropTableBuilder(): void
    {
        $dropTableBuilder = $this->createMock(DropTableBuilderInterface::class);
        $dropTableBuilder->expects($this->once())
            ->method('dropTable')
            ->with('users')
            ->willReturn($dropTableBuilder);

        $this->dropTableFactory->expects($this->once())
            ->method('create')
            ->willReturn($dropTableBuilder);

        $result = $this->ddlManager->dropTable('users');

        $this->assertInstanceOf(DropTableBuilderInterface::class, $result);
    }

    public function testTruncateTableReturnsTruncateTableBuilder(): void
    {
        $truncateTableBuilder = $this->createMock(TruncateTableBuilderInterface::class);
        $truncateTableBuilder->expects($this->once())
            ->method('truncateTable')
            ->with('users')
            ->willReturn($truncateTableBuilder);

        $this->truncateTableFactory->expects($this->once())
            ->method('create')
            ->willReturn($truncateTableBuilder);

        $result = $this->ddlManager->truncateTable('users');

        $this->assertInstanceOf(TruncateTableBuilderInterface::class, $result);
    }

    public function testCreateTableCreatesNewBuilderEachTime(): void
    {
        $createTableBuilder1 = $this->createMock(CreateTableBuilderInterface::class);
        $createTableBuilder1->method('createTable')->willReturn($createTableBuilder1);

        $this->createTableFactory->expects($this->once())
            ->method('create')
            ->willReturn($createTableBuilder1);

        // First call
        $result1 = $this->ddlManager->createTable('users');
        
        // Second call should clone the prototype
        $result2 = $this->ddlManager->createTable('orders');

        // Both should be CreateTableBuilderInterface but different instances
        $this->assertInstanceOf(CreateTableBuilderInterface::class, $result1);
        $this->assertInstanceOf(CreateTableBuilderInterface::class, $result2);
    }
}
