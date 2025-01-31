<?php
namespace Concept\DBAL\DML\Builder\Contract;

interface JoinableInterface
{
    /**
     * Add a JOIN to the query
     * Pass "table", "alias" and the expressions to join on
     * Expressions can be strings, Stringables or SqlExpressionInterfaces
     * 
     * 
     * @param string|Stringable|SqlExpressionInterface $table         The table to join
     * @param string                                    $alias          The alias for the table
     * @param string|Stringable|SqlExpressionInterface ...$expressions The expressions to join on
     * 
     * @return static
     */
    public function join($table, string $alias, ...$expressions): static;

    /**
     * Add a LEFT JOIN to the query
     * Pass "table", "alias" and the expressions to join on
     * Expressions can be strings, Stringables or SqlExpressionInterfaces
     * 
     * 
     * @param string|Stringable|SqlExpressionInterface $table         The table to join
     * @param string                                    $alias          The alias for the table
     * @param string|Stringable|SqlExpressionInterface ...$expressions The expressions to join on
     * 
     * @return static
     */
    public function leftJoin($table, string $alias, ...$expressions): static;

    /**
     * Add a RIGHT JOIN to the query
     * Pass "table", "alias" and the expressions to join on
     * Expressions can be strings, Stringables or SqlExpressionInterfaces
     * 
     * 
     * @param string|Stringable|SqlExpressionInterface $table         The table to join
     * @param string                                    $alias          The alias for the table
     * @param string|Stringable|SqlExpressionInterface ...$expressions The expressions to join on
     * 
     * @return static
     */
    public function rightJoin($table, string $alias, ...$expressions): static;

    public function joinUsing($table, string $alias, ...$columns): static;
}