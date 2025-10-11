<?php
namespace Concept\DBAL\DDL;

use Concept\DBAL\DDL\Builder\CreateTableBuilderInterface;
use Concept\DBAL\DDL\Builder\AlterTableBuilderInterface;
use Concept\DBAL\DDL\Builder\DropTableBuilderInterface;
use Concept\DBAL\DDL\Builder\TruncateTableBuilderInterface;
use Concept\DBAL\DML\Expression\Contract\SqlExpressionAwareInterface;

interface DdlManagerInterface 
   extends 
      SqlExpressionAwareInterface
{
   public function createTable(string $table): CreateTableBuilderInterface;

   public function alterTable(string $table): AlterTableBuilderInterface;

   public function dropTable(string $table): DropTableBuilderInterface;

   public function truncateTable(string $table): TruncateTableBuilderInterface;
}
