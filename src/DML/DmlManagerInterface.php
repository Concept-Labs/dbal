<?php
namespace Concept\DBAL\DML;

use Concept\DBAL\DML\Builder\DeleteBuilderInterface;
use Concept\DBAL\DML\Builder\InsertBuilderInterface;
use Concept\DBAL\DML\Builder\SelectBuilderInterface;
use Concept\DBAL\DML\Builder\UpdateBuilderInterface;
use Concept\DBAL\DML\Expression\Contract\SqlExpressionAwareInterface;

interface DmlManagerInterface 
   extends 
      SqlExpressionAwareInterface
{
   public function select(...$columns): SelectBuilderInterface;

   public function insert(?string $table = null): InsertBuilderInterface;

   public function update(string|array $table): UpdateBuilderInterface;

   public function delete(?string $table = null): DeleteBuilderInterface;
}