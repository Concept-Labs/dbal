<?php
namespace Concept\DBAL\DML;

use Concept\DBAL\DML\Builder\InsertBuilderInterface;
use Concept\DBAL\DML\Builder\SelectBuilderInterface;
use Concept\DBAL\DML\Expression\SqlExpressionInterface;
use Concept\Di\InjectableInterface;

interface DmlManagerInterface extends InjectableInterface
{

   public function expression(...$expressions): SqlExpressionInterface;

   public function select(...$columns): SelectBuilderInterface;

   public function insert(?string $table = null): InsertBuilderInterface;
}