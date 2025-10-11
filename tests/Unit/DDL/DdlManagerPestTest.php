<?php

use Concept\DBAL\DDL\DdlManager;
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

beforeEach(function () {
    $this->connection = Mockery::mock(ConnectionInterface::class);
    $this->expression = Mockery::mock(SqlExpressionInterface::class);
    $this->createTableFactory = Mockery::mock(CreateTableBuilderFactoryInterface::class);
    $this->alterTableFactory = Mockery::mock(AlterTableBuilderFactoryInterface::class);
    $this->dropTableFactory = Mockery::mock(DropTableBuilderFactoryInterface::class);
    $this->truncateTableFactory = Mockery::mock(TruncateTableBuilderFactoryInterface::class);

    $this->ddlManager = new DdlManager(
        $this->connection,
        $this->expression,
        $this->createTableFactory,
        $this->alterTableFactory,
        $this->dropTableFactory,
        $this->truncateTableFactory
    );
});

afterEach(function () {
    Mockery::close();
});

it('returns a create table builder when calling createTable', function () {
    $createTableBuilder = Mockery::mock(CreateTableBuilderInterface::class);
    $createTableBuilder->shouldReceive('createTable')
        ->once()
        ->with('users')
        ->andReturn($createTableBuilder);

    $this->createTableFactory->shouldReceive('create')
        ->once()
        ->andReturn($createTableBuilder);

    $result = $this->ddlManager->createTable('users');

    expect($result)->toBeInstanceOf(CreateTableBuilderInterface::class);
});

it('returns an alter table builder when calling alterTable', function () {
    $alterTableBuilder = Mockery::mock(AlterTableBuilderInterface::class);
    $alterTableBuilder->shouldReceive('alterTable')
        ->once()
        ->with('users')
        ->andReturn($alterTableBuilder);

    $this->alterTableFactory->shouldReceive('create')
        ->once()
        ->andReturn($alterTableBuilder);

    $result = $this->ddlManager->alterTable('users');

    expect($result)->toBeInstanceOf(AlterTableBuilderInterface::class);
});

it('returns a drop table builder when calling dropTable', function () {
    $dropTableBuilder = Mockery::mock(DropTableBuilderInterface::class);
    $dropTableBuilder->shouldReceive('dropTable')
        ->once()
        ->with('users')
        ->andReturn($dropTableBuilder);

    $this->dropTableFactory->shouldReceive('create')
        ->once()
        ->andReturn($dropTableBuilder);

    $result = $this->ddlManager->dropTable('users');

    expect($result)->toBeInstanceOf(DropTableBuilderInterface::class);
});

it('returns a truncate table builder when calling truncateTable', function () {
    $truncateTableBuilder = Mockery::mock(TruncateTableBuilderInterface::class);
    $truncateTableBuilder->shouldReceive('truncateTable')
        ->once()
        ->with('logs')
        ->andReturn($truncateTableBuilder);

    $this->truncateTableFactory->shouldReceive('create')
        ->once()
        ->andReturn($truncateTableBuilder);

    $result = $this->ddlManager->truncateTable('logs');

    expect($result)->toBeInstanceOf(TruncateTableBuilderInterface::class);
});

it('creates a new builder instance for each createTable call', function () {
    $createTableBuilder = Mockery::mock(CreateTableBuilderInterface::class);
    $createTableBuilder->shouldReceive('createTable')
        ->andReturn($createTableBuilder);

    $this->createTableFactory->shouldReceive('create')
        ->once()
        ->andReturn($createTableBuilder);

    $result1 = $this->ddlManager->createTable('users');
    $result2 = $this->ddlManager->createTable('orders');

    expect($result1)->toBeInstanceOf(CreateTableBuilderInterface::class)
        ->and($result2)->toBeInstanceOf(CreateTableBuilderInterface::class);
});

it('can chain multiple table operations', function () {
    // Create table
    $createTableBuilder = Mockery::mock(CreateTableBuilderInterface::class);
    $createTableBuilder->shouldReceive('createTable')
        ->once()
        ->with('users')
        ->andReturn($createTableBuilder);

    $this->createTableFactory->shouldReceive('create')
        ->once()
        ->andReturn($createTableBuilder);

    // Alter table
    $alterTableBuilder = Mockery::mock(AlterTableBuilderInterface::class);
    $alterTableBuilder->shouldReceive('alterTable')
        ->once()
        ->with('users')
        ->andReturn($alterTableBuilder);

    $this->alterTableFactory->shouldReceive('create')
        ->once()
        ->andReturn($alterTableBuilder);

    $create = $this->ddlManager->createTable('users');
    $alter = $this->ddlManager->alterTable('users');

    expect($create)->toBeInstanceOf(CreateTableBuilderInterface::class)
        ->and($alter)->toBeInstanceOf(AlterTableBuilderInterface::class);
});
