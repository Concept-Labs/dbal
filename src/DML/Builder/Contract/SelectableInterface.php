<?php
namespace Concept\DBAL\DML\Builder\Contract;

use Concept\DBAL\DML\Builder\SqlBuilderInterface;
use Concept\DBAL\DML\Expression\SqlExpressionInterface;
use Stringable;

interface SelectableInterface
{
    /**
     * Add a DISTINCT clause to the query.
     *
     * @return static
     */
    public function distinct(): static;

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
     * @param string|Stringable|SqlExpressionInterface|SqlBuilderInterface|array ...$columns The columns to add
     * 
     * @return static
     * 
     * @throws \InvalidArgumentException If the columns are empty
     */
    public function select(string|Stringable|SqlExpressionInterface|SqlBuilderInterface|array ...$columns): static;

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
     * @param string|Stringable|SqlExpressionInterface|array<string|Stringable|SqlExpressionInterface> ...$tables The tables to add
     * 
     * @return static
     * 
     * @throws \InvalidArgumentException If the tables are empty
     */
    public function from(...$tables): static;

}