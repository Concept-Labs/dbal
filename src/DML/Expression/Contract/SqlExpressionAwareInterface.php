<?php
namespace Concept\DBAL\DML\Expression\Contract;

use Concept\DBAL\DML\Expression\SqlExpressionInterface;

interface SqlExpressionAwareInterface
{
    
    public function expression(...$expressions): SqlExpressionInterface;
    //public function expr(...$expressions): SqlExpressionInterface;
    public function sql(...$expressions): SqlExpressionInterface;
}