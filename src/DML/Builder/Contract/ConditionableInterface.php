<?php
namespace Concept\DBAL\DML\Builder\Contract;

use Concept\DBAL\Expression\SqlExpressionInterface;
use Stringable;

interface ConditionableInterface
{
/**
     * Add a WHERE to the query
     * Pass the conditions as arguments
     * Agruments can be strings, Stringables or QueryExpressionInterfaces
     * ('condition', 'condition', ...)
     * To set column or expression aliases, 
     * pass an array with the column/expression as key and the alias as value:
     * ('condition', ['alias' => 'condition'], ...)
     * ('condition', 'condition', ['alias' => <QueryExpressionInterface>, ...]
     * 
     * 
     * @param string|Stringable|SqlExpressionInterface ...$conditions The conditions to add
     * 
     * @return static
     * 
     * @throws \InvalidArgumentException If the conditions are empty
     */
    public function where(string|SqlExpressionInterface ...$conditions): static;

    /**
     * Add a WHERE IN condition to the query
     * 
     * @param string|SqlExpressionInterface $column The column to add
     * @param array|SqlExpressionInterface $values The values to add
     * 
     * @return static
     */
    public function whereIn(string|SqlExpressionInterface $column, array|SqlExpressionInterface $values): static;
    
    /**
     * Add LIKE condition to the query
     * 
     * @param string $column The column to add
     * @param string|SqlExpressionInterface $value The value to add
     * 
     * @return static
     */
    public function whereLike(string|SqlExpressionInterface $column, string|SqlExpressionInterface $value): static;

    /**
     * Add a OR WHERE to the query
     * Pass the conditions as arguments
     * Agruments can be strings, Stringables or QueryExpressionInterfaces
     * ('condition', 'condition', ...)
     * To set column or expression aliases, 
     * pass an array with the column/expression as key and the alias as value:
     * ('condition', ['alias' => 'condition'], ...)
     * ('condition', 'condition', ['alias' => <QueryExpressionInterface>, ...]
     * 
     * 
     * @param string|SqlExpressionInterface ...$conditions The conditions to add
     * 
     * @return static
     * 
     * @throws \InvalidArgumentException If the conditions are empty
     */
    public function orWhere(string|SqlExpressionInterface ...$conditions): static;
  
    /**
     * Add a CASE condition to the query
     */
    public function whereCase(
        string|array|SqlExpressionInterface $condition,
        string|SqlExpressionInterface $thenValue,
        string|SqlExpressionInterface|null $elseValue = null
    ): static;
    
    /**
     * Add a BETWEEN condition to the query
     * @todo impllement
     */
    public function between(): static;

    /**
     * Add a HAVING to the query
     * Pass the conditions as arguments
     * Agruments can be strings or QueryExpressionInterfaces
     * ('condition', 'condition', ...)
     * To set column or expression aliases, 
     * pass an array with the column/expression as key and the alias as value:
     * ('condition', ['alias' => 'condition'], ...)
     * ('condition', 'condition', ['alias' => <QueryExpressionInterface>, ...]
     * 
     * 
     * @param string|SqlExpressionInterface ...$conditions The conditions to add
     * 
     * @return static
     * 
     * @throws \InvalidArgumentException If the conditions are empty
     */
    public function having(string|SqlExpressionInterface ...$conditions): static;

    /**
     * Add a OR HAVING to the query
     * Pass the conditions as arguments
     * Agruments can be strings or QueryExpressionInterfaces
     * ('condition', 'condition', ...)
     * To set column or expression aliases, 
     * pass an array with the column/expression as key and the alias as value:
     * ('condition', ['alias' => 'condition'], ...)
     * ('condition', 'condition', ['alias' => <QueryExpressionInterface>, ...]
     * 
     * 
     * @param string|SqlExpressionInterface ...$conditions The conditions to add
     * 
     * @return static
     * 
     * @throws \InvalidArgumentException If the conditions are empty
     */
    public function orHaving(string|SqlExpressionInterface ...$conditions): static;

    /**
     * Add a HAVING LIKE condition to the query
     * 
     * @param string|SqlExpressionInterface $column The column to add
     * @param array|SqlExpressionInterface $values The values to add
     * 
     * @return static
     */
    public function havingLike(string|SqlExpressionInterface $column, string|SqlExpressionInterface $value): static;

    /**
     * Add a HAVING IN condition to the query
     * 
     * @param string|SqlExpressionInterface $column The column to add
     * @param array|SqlExpressionInterface $values The values to add
     * 
     * @return static
     */
    public function havingIn(string|SqlExpressionInterface $column, array|SqlExpressionInterface $values): static;
    
}