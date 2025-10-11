<?php
namespace Concept\DBAL\DDL\Builder;

use Concept\DBAL\DML\Builder\SqlBuilderInterface;

interface DropTableBuilderInterface extends SqlBuilderInterface
{
    /**
     * Initialize the query as a DROP TABLE
     * 
     * @return static
     */
    public function dropTable(string $table): static;

    /**
     * Add IF EXISTS clause
     * 
     * @return static
     */
    public function ifExists(): static;

    /**
     * Add CASCADE option
     * 
     * @return static
     */
    public function cascade(): static;

    /**
     * Add RESTRICT option
     * 
     * @return static
     */
    public function restrict(): static;
}
