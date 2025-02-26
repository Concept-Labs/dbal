<?php
namespace Concept\DBAL\DML\Expression\Contract;

use Concept\DBAL\DML\Expression\KeywordEnum;
use Concept\DBAL\DML\Expression\SqlExpressionInterface;

trait AggregateFunctionsTrait
{
    // public function sum(string|SqlExpressionInterface $column): static
    // {
    //     return $this->fn(KeywordEnum::SUM, $column);
    // }

    // public function avg(string|SqlExpressionInterface $column): static
    // {
    //     return $this->fn(KeywordEnum::AVG, $column);
    // }

    // public function min(string|SqlExpressionInterface $column): static
    // {
    //     return $this->fn(KeywordEnum::MIN, $column);
    // }

    // public function max(string|SqlExpressionInterface $column): static
    // {
    //     return $this->fn(KeywordEnum::MAX, $column);
    // }

    // public function count(string|SqlExpressionInterface $column = '*'): static
    // {
    //     return $this->fn(KeywordEnum::COUNT, $column);
    // }

    public function fn(string $function, string|SqlExpressionInterface $column): static
    {
        return $this->prototype()->push(
            $this->keyword($function),
            $this->prototype()->push(
                $column instanceof SqlExpressionInterface 
                ? $column 
                : $this->identifier($column)
            )->wrap('(', ')')
            
        );
    }
}