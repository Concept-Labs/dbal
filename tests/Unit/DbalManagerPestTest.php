<?php

use Concept\DBAL\DbalManager;
use Concept\DBAL\DML\DmlManagerInterface;
use Concept\DBAL\DDL\DdlManagerInterface;

beforeEach(function () {
    $this->dmlManager = Mockery::mock(DmlManagerInterface::class);
    $this->ddlManager = Mockery::mock(DdlManagerInterface::class);

    $this->dbalManager = new DbalManager($this->dmlManager, $this->ddlManager);
});

afterEach(function () {
    Mockery::close();
});

it('returns the DML manager when calling dml', function () {
    $result = $this->dbalManager->dml();

    expect($result)->toBe($this->dmlManager)
        ->and($result)->toBeInstanceOf(DmlManagerInterface::class);
});

it('returns the DDL manager when calling ddl', function () {
    $result = $this->dbalManager->ddl();

    expect($result)->toBe($this->ddlManager)
        ->and($result)->toBeInstanceOf(DdlManagerInterface::class);
});

it('always returns the same DML manager instance', function () {
    $result1 = $this->dbalManager->dml();
    $result2 = $this->dbalManager->dml();

    expect($result1)->toBe($result2);
});

it('always returns the same DDL manager instance', function () {
    $result1 = $this->dbalManager->ddl();
    $result2 = $this->dbalManager->ddl();

    expect($result1)->toBe($result2);
});

it('provides both DML and DDL managers independently', function () {
    $dml = $this->dbalManager->dml();
    $ddl = $this->dbalManager->ddl();

    expect($dml)->toBeInstanceOf(DmlManagerInterface::class)
        ->and($ddl)->toBeInstanceOf(DdlManagerInterface::class)
        ->and($dml)->not->toBe($ddl);
});
