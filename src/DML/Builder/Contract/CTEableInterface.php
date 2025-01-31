<?php
namespace Concept\DBAL\DML\Builder\Contract;

use Concept\DBAL\DML\Expression\SqlExpressionInterface;

interface CTEableInterface
{
    /**
     * Add a common table expression
     *
     * @param string $name
     * @param SqlExpressionInterface $select
     * @return static
     */
    public function with(string $name, SqlExpressionInterface $select): static;
}