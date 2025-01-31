<?php
namespace Concept\DBAL\DML\Builder\Contract;

interface OrderableInterface
{
    /**
     * Add a ORDER BY to the query
     * Pass the columns as arguments
     * Agruments can be strings, Stringables or QueryExpressionInterfaces
     * ('column', 'column', ...)
     * To set column ordering types pass an array with the column as key and the ordering type as value:
     * ('column', ['column' => 'ASC'], ...)
     * 
     * @param string|Stringable|QueryExpressionInterface ...$columns The columns to add
     * 
     * @return static
     * 
     * @throws \InvalidArgumentException If the columns contains incorrect values
     */
    public function orderBy(...$columns): static;

}