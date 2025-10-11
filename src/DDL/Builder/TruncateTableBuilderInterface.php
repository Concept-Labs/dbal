<?php
namespace Concept\DBAL\DDL\Builder;

use Concept\DBAL\DML\Builder\SqlBuilderInterface;

interface TruncateTableBuilderInterface extends SqlBuilderInterface
{
    /**
     * Initialize the query as a TRUNCATE TABLE
     * 
     * @return static
     */
    public function truncateTable(string $table): static;
}
