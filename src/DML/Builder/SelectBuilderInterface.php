<?php
namespace Concept\DBAL\DML\Builder;


interface SelectBuilderInterface extends SqlBuilderInterface
{
     /**
     * Add a DISTINCT to the query
     * 
     * @return self
     */
    public function distinct(): self;

    /**
     * Add a SELECT to the query
     * Pass the columns as arguments
     * Agruments can be strings, Stringables or SqlExpressionInterfaces
     *  ('column', 'column', ...)
     * To set column or expression aliases, 
     *  pass an array with the column/expression as key and the alias as value:
     *  ('column', ['alias' => 'column'], ...)
     *  ('column', 'column', ['alias' => <SqlExpressionInterface>, ...]
     * 
     * 
     * @param string|Stringable|SqlExpressionInterface ...$columns The columns to add
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException If the columns are empty
     */
    public function select(...$columns): self;

    /**
     * Add a FROM to the query
     * Pass the tables as arguments
     * Agruments can be strings, Stringables or SqlExpressionInterfaces
     * ('table', 'table', ...)
     * To set table aliases,
     * pass an array with the table as key and the alias as value:
     * ('table', ['alias' => 'table'], ...)
     * ('table', 'table', ['alias' => <SqlExpressionInterface>, ...]
     * 
     * @param string|Stringable|SqlExpressionInterface ...$tables The tables to add
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException If the tables are empty
     */
    public function from(...$tables): self;

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
     * @return self
     */
    public function join($table, string $alias, ...$expressions): self;

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
     * @return self
     */
    public function leftJoin($table, string $alias, ...$expressions): self;

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
     * @return self
     */
    public function rightJoin($table, string $alias, ...$expressions): self;

    /**
     * Add a HAVING to the query
     * Pass the conditions as arguments
     * Agruments can be strings, Stringables or QueryExpressionInterfaces
     * ('condition', 'condition', ...)
     * To set column or expression aliases, 
     * pass an array with the column/expression as key and the alias as value:
     * ('condition', ['alias' => 'condition'], ...)
     * ('condition', 'condition', ['alias' => <QueryExpressionInterface>, ...]
     * 
     * 
     * @param string|Stringable|QueryExpressionInterface ...$conditions The conditions to add
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException If the conditions are empty
     */
    public function having(...$conditions): self;

    /**
     * Add a OR HAVING to the query
     * Pass the conditions as arguments
     * Agruments can be strings, Stringables or QueryExpressionInterfaces
     * ('condition', 'condition', ...)
     * To set column or expression aliases, 
     * pass an array with the column/expression as key and the alias as value:
     * ('condition', ['alias' => 'condition'], ...)
     * ('condition', 'condition', ['alias' => <QueryExpressionInterface>, ...]
     * 
     * 
     * @param string|Stringable|QueryExpressionInterface ...$conditions The conditions to add
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException If the conditions are empty
     */
    public function orHaving(...$conditions): self;

    /**
     * Add a GROUP BY to the query
     * Pass the columns as arguments
     * Agruments can be strings, Stringables or QueryExpressionInterfaces
     * ('column', 'column', ...)
     * To set column or expression aliases, 
     * pass an array with the column/expression as key and the alias as value:
     * ('column', ['alias' => 'column'], ...)
     * ('column', 'column', ['alias' => <QueryExpressionInterface>, ...]
     * 
     * 
     * @param string|Stringable|QueryExpressionInterface ...$columns The columns to add
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException If the columns are empty
     */
    public function groupBy(...$columns): self;

    /**
     * Add a GROUP to the query
     * 
     * @see groupBy()
     */
    public function group(...$columns): self;


    /**
     * Add a ORDER BY to the query
     * Pass the columns as arguments
     * Agruments can be strings, Stringables or QueryExpressionInterfaces
     * ('column', 'column', ...)
     * To set column or expression aliases, 
     * pass an array with the column/expression as key and the alias as value:
     * ('column', ['alias' => 'column'], ...)
     * ('column', 'column', ['alias' => <QueryExpressionInterface>, ...]
     * 
     * 
     * @param string|Stringable|QueryExpressionInterface ...$columns The columns to add
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException If the columns are empty
     */
    public function orderBy(...$columns): self;
}