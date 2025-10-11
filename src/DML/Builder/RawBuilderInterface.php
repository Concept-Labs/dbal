<?php
namespace Concept\DBAL\DML\Builder;

use Concept\DBAL\Expression\SqlExpressionInterface;

interface RawBuilderInterface 
    extends 
        SqlBuilderInterface
        
{
    /**
     * @param string}SqlExpressionInterface $sql
     * 
     * @return RawBuilderInterface
     */
    public function raw(string|SqlExpressionInterface ...$sql): RawBuilderInterface;
}