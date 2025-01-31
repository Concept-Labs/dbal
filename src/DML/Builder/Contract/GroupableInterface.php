<?php
namespace Concept\DBAL\DML\Builder\Contract;

interface GroupableInterface
{
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
     * @return static
     * 
     * @throws \InvalidArgumentException If the columns are empty
     */
    public function groupBy(...$columns): static;

    /**
     * Add a GROUP to the query
     * 
     * @see groupBy()
     */
    public function group(...$columns): static;

}