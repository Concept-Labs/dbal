<?php
namespace Concept\DBAL\DML\Builder\Contract;

use Concept\DBAL\Expression\SqlExpressionInterface;

interface UnionableInterface
{
    /**
     * Add a UNION clause to the query.
     *
     * @param SqlExpressionInterface $query
     * @return static
     */
    public function union(SqlExpressionInterface $query): static;

    /**
     * Add a UNION ALL clause to the query.
     *
     * @param SqlExpressionInterface $query
     * @return static
     */
    //public function unionAll(SqlExpressionInterface $query): static;
}