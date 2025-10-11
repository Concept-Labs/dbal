<?php
namespace Concept\DBAL\Expression\Contract;

use Concept\DBAL\Expression\SqlExpressionInterface;

interface SqlExpressionAwareInterface
{
    
    public function expression(...$expressions): SqlExpressionInterface;
    //public function expr(...$expressions): SqlExpressionInterface;
    public function sql(...$expressions): SqlExpressionInterface;
}