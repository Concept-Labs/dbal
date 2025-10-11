<?php 
namespace Concept\DBAL\Expression\Contract;

use Concept\DBAL\Expression\SqlExpressionInterface;

interface AggregateFunctionsInterface
{
    // public function avg(string|SqlExpressionInterface $column);
    // public function count(string|SqlExpressionInterface $column);
    // public function max(string|SqlExpressionInterface $column);
    // public function min(string|SqlExpressionInterface $column);
    // public function sum(string|SqlExpressionInterface $column);
    public function fn(string $function, string|SqlExpressionInterface $column);

}