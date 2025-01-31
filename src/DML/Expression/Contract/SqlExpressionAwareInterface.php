<?php
namespace Concept\DBAL\DML\Expression\Contract;

use Concept\DBAL\DML\Expression\SqlExpressionFactoryInterface;
use Concept\DBAL\DML\Expression\SqlExpressionInterface;

interface SqlExpressionAwareInterface
{
    public function withSqlExpressionFactory(SqlExpressionFactoryInterface $sqlExpressionFactory): void;
    public function expression(...$expressions): SqlExpressionInterface;
    //public function expr(...$expressions): SqlExpressionInterface;
    //public function e(...$expressions): SqlExpressionInterface;
}