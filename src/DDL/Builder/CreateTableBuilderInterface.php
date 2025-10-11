<?php
namespace Concept\DBAL\DDL\Builder;

use Concept\DBAL\DML\Builder\SqlBuilderInterface;

interface CreateTableBuilderInterface extends SqlBuilderInterface
{
    /**
     * Initialize the query as a CREATE TABLE
     * 
     * @return static
     */
    public function createTable(string $table): static;

    /**
     * Add IF NOT EXISTS clause
     * 
     * @return static
     */
    public function ifNotExists(): static;

    /**
     * Add a column definition
     * 
     * @return static
     */
    public function addColumn(string $name, string $type, array $options = []): static;

    /**
     * Add a primary key constraint
     * 
     * @return static
     */
    public function primaryKey(string|array $columns): static;

    /**
     * Add a foreign key constraint
     * 
     * @return static
     */
    public function foreignKey(string $column, string $referencedTable, string $referencedColumn, array $options = []): static;

    /**
     * Add a unique constraint
     * 
     * @return static
     */
    public function unique(string|array $columns): static;

    /**
     * Add an index
     * 
     * @return static
     */
    public function index(string|array $columns, ?string $name = null): static;

    /**
     * Set table options (ENGINE, CHARSET, etc.)
     * 
     * @return static
     */
    public function options(array $options): static;
}
