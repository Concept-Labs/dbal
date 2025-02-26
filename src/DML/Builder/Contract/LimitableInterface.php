<?php
namespace Concept\DBAL\DML\Builder\Contract;

interface LimitableInterface
{
  /**
     * Add a LIMIT to the query
     * 
     * @param int $limit  The limit value
     * @param int|null $offset The offset value
     * 
     * @return static
     */

    public function limit(int $limit, ?int $offset = null): static;
}