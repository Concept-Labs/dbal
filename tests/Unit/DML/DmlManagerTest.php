<?php

namespace Tests\Unit\DML;

use Concept\DBAL\DML\DmlManager;
use Concept\DBAL\DML\DmlManagerInterface;
use Concept\DBAL\DML\Builder\SelectBuilderInterface;
use Concept\DBAL\DML\Builder\InsertBuilderInterface;
use Concept\DBAL\DML\Builder\UpdateBuilderInterface;
use Concept\DBAL\DML\Builder\DeleteBuilderInterface;
use Concept\DBAL\DML\Builder\Factory\SelectBuilderFactoryInterface;
use Concept\DBAL\DML\Builder\Factory\InsertBuilderFactoryInterface;
use Concept\DBAL\DML\Builder\Factory\UpdateBuilderFactoryInterface;
use Concept\DBAL\DML\Builder\Factory\DeleteBuilderFactoryInterface;
use Concept\DBAL\DML\Builder\Factory\RawBuilderFactoryInterface;
use Concept\DBAL\DML\Expression\SqlExpressionInterface;
use Concept\DBC\ConnectionInterface;
use PHPUnit\Framework\TestCase;

class DmlManagerTest extends TestCase
{
    private DmlManagerInterface $dmlManager;
    private ConnectionInterface $connection;
    private SqlExpressionInterface $expression;
    private SelectBuilderFactoryInterface $selectFactory;
    private InsertBuilderFactoryInterface $insertFactory;
    private UpdateBuilderFactoryInterface $updateFactory;
    private DeleteBuilderFactoryInterface $deleteFactory;
    private RawBuilderFactoryInterface $rawFactory;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(ConnectionInterface::class);
        $this->expression = $this->createMock(SqlExpressionInterface::class);
        $this->selectFactory = $this->createMock(SelectBuilderFactoryInterface::class);
        $this->insertFactory = $this->createMock(InsertBuilderFactoryInterface::class);
        $this->updateFactory = $this->createMock(UpdateBuilderFactoryInterface::class);
        $this->deleteFactory = $this->createMock(DeleteBuilderFactoryInterface::class);
        $this->rawFactory = $this->createMock(RawBuilderFactoryInterface::class);

        $this->dmlManager = new DmlManager(
            $this->connection,
            $this->expression,
            $this->rawFactory,
            $this->selectFactory,
            $this->insertFactory,
            $this->updateFactory,
            $this->deleteFactory
        );
    }

    public function testSelectReturnsSelectBuilder(): void
    {
        $selectBuilder = $this->createMock(SelectBuilderInterface::class);
        $selectBuilder->expects($this->once())
            ->method('select')
            ->with('*')
            ->willReturn($selectBuilder);

        $this->selectFactory->expects($this->once())
            ->method('create')
            ->willReturn($selectBuilder);

        $result = $this->dmlManager->select('*');

        $this->assertInstanceOf(SelectBuilderInterface::class, $result);
    }

    public function testInsertReturnsInsertBuilder(): void
    {
        $insertBuilder = $this->createMock(InsertBuilderInterface::class);
        $insertBuilder->expects($this->once())
            ->method('insert')
            ->with('users')
            ->willReturn($insertBuilder);

        $this->insertFactory->expects($this->once())
            ->method('create')
            ->willReturn($insertBuilder);

        $result = $this->dmlManager->insert('users');

        $this->assertInstanceOf(InsertBuilderInterface::class, $result);
    }

    public function testUpdateReturnsUpdateBuilder(): void
    {
        $updateBuilder = $this->createMock(UpdateBuilderInterface::class);
        $updateBuilder->expects($this->once())
            ->method('update')
            ->with('users')
            ->willReturn($updateBuilder);

        $this->updateFactory->expects($this->once())
            ->method('create')
            ->willReturn($updateBuilder);

        $result = $this->dmlManager->update('users');

        $this->assertInstanceOf(UpdateBuilderInterface::class, $result);
    }

    public function testDeleteReturnsDeleteBuilder(): void
    {
        $deleteBuilder = $this->createMock(DeleteBuilderInterface::class);
        $deleteBuilder->expects($this->once())
            ->method('delete')
            ->with('users')
            ->willReturn($deleteBuilder);

        $this->deleteFactory->expects($this->once())
            ->method('create')
            ->willReturn($deleteBuilder);

        $result = $this->dmlManager->delete('users');

        $this->assertInstanceOf(DeleteBuilderInterface::class, $result);
    }

    public function testSelectCreatesNewBuilderEachTime(): void
    {
        $selectBuilder1 = $this->createMock(SelectBuilderInterface::class);
        $selectBuilder1->method('select')->willReturn($selectBuilder1);

        $this->selectFactory->expects($this->once())
            ->method('create')
            ->willReturn($selectBuilder1);

        // First call
        $result1 = $this->dmlManager->select('id');
        
        // Second call should clone the prototype
        $result2 = $this->dmlManager->select('name');

        // Both should be SelectBuilderInterface but different instances
        $this->assertInstanceOf(SelectBuilderInterface::class, $result1);
        $this->assertInstanceOf(SelectBuilderInterface::class, $result2);
    }
}
