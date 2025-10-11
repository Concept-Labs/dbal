<?php

use Concept\DBAL\DML\DmlManager;
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

beforeEach(function () {
    $this->connection = Mockery::mock(ConnectionInterface::class);
    $this->expression = Mockery::mock(SqlExpressionInterface::class);
    $this->selectFactory = Mockery::mock(SelectBuilderFactoryInterface::class);
    $this->insertFactory = Mockery::mock(InsertBuilderFactoryInterface::class);
    $this->updateFactory = Mockery::mock(UpdateBuilderFactoryInterface::class);
    $this->deleteFactory = Mockery::mock(DeleteBuilderFactoryInterface::class);
    $this->rawFactory = Mockery::mock(RawBuilderFactoryInterface::class);

    $this->dmlManager = new DmlManager(
        $this->connection,
        $this->expression,
        $this->rawFactory,
        $this->selectFactory,
        $this->insertFactory,
        $this->updateFactory,
        $this->deleteFactory
    );
});

afterEach(function () {
    Mockery::close();
});

it('returns a select builder when calling select', function () {
    $selectBuilder = Mockery::mock(SelectBuilderInterface::class);
    $selectBuilder->shouldReceive('select')
        ->once()
        ->with('*')
        ->andReturn($selectBuilder);

    $this->selectFactory->shouldReceive('create')
        ->once()
        ->andReturn($selectBuilder);

    $result = $this->dmlManager->select('*');

    expect($result)->toBeInstanceOf(SelectBuilderInterface::class);
});

it('returns an insert builder when calling insert', function () {
    $insertBuilder = Mockery::mock(InsertBuilderInterface::class);
    $insertBuilder->shouldReceive('insert')
        ->once()
        ->with('users')
        ->andReturn($insertBuilder);

    $this->insertFactory->shouldReceive('create')
        ->once()
        ->andReturn($insertBuilder);

    $result = $this->dmlManager->insert('users');

    expect($result)->toBeInstanceOf(InsertBuilderInterface::class);
});

it('returns an update builder when calling update', function () {
    $updateBuilder = Mockery::mock(UpdateBuilderInterface::class);
    $updateBuilder->shouldReceive('update')
        ->once()
        ->with('users')
        ->andReturn($updateBuilder);

    $this->updateFactory->shouldReceive('create')
        ->once()
        ->andReturn($updateBuilder);

    $result = $this->dmlManager->update('users');

    expect($result)->toBeInstanceOf(UpdateBuilderInterface::class);
});

it('returns a delete builder when calling delete', function () {
    $deleteBuilder = Mockery::mock(DeleteBuilderInterface::class);
    $deleteBuilder->shouldReceive('delete')
        ->once()
        ->with('users')
        ->andReturn($deleteBuilder);

    $this->deleteFactory->shouldReceive('create')
        ->once()
        ->andReturn($deleteBuilder);

    $result = $this->dmlManager->delete('users');

    expect($result)->toBeInstanceOf(DeleteBuilderInterface::class);
});

it('creates a new builder instance for each select call', function () {
    $selectBuilder = Mockery::mock(SelectBuilderInterface::class);
    $selectBuilder->shouldReceive('select')
        ->andReturn($selectBuilder);

    $this->selectFactory->shouldReceive('create')
        ->once()
        ->andReturn($selectBuilder);

    $result1 = $this->dmlManager->select('id');
    $result2 = $this->dmlManager->select('name');

    expect($result1)->toBeInstanceOf(SelectBuilderInterface::class)
        ->and($result2)->toBeInstanceOf(SelectBuilderInterface::class);
});

it('passes multiple columns to select builder', function () {
    $selectBuilder = Mockery::mock(SelectBuilderInterface::class);
    $selectBuilder->shouldReceive('select')
        ->once()
        ->with('id', 'name', 'email')
        ->andReturn($selectBuilder);

    $this->selectFactory->shouldReceive('create')
        ->once()
        ->andReturn($selectBuilder);

    $result = $this->dmlManager->select('id', 'name', 'email');

    expect($result)->toBeInstanceOf(SelectBuilderInterface::class);
});
