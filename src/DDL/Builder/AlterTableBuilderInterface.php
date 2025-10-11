<?php
namespace Concept\DBAL\DDL\Builder;

use Concept\DBAL\DML\Builder\SqlBuilderInterface;

interface AlterTableBuilderInterface extends SqlBuilderInterface
{
    /**
     * Initialize the query as an ALTER TABLE
     * 
     * @return static
     */
    public function alterTable(string $table): static;

    /**
     * Add a column
     * 
     * @return static
     */
    public function addColumn(string $name, string $type, array $options = []): static;

    /**
     * Modify a column
     * 
     * @return static
     */
    public function modifyColumn(string $name, string $type, array $options = []): static;

    /**
     * Drop a column
     * 
     * @return static
     */
    public function dropColumn(string $name): static;

    /**
     * Rename a column
     * 
     * @return static
     */
    public function renameColumn(string $oldName, string $newName): static;

    /**
     * Add a constraint (primary key, foreign key, unique, etc.)
     * 
     * @return static
     */
    public function addConstraint(string $type, string|array $columns, array $options = []): static;

    /**
     * Drop a constraint
     * 
     * @return static
     */
    public function dropConstraint(string $name): static;

    /**
     * Rename the table
     * 
     * @return static
     */
    public function renameTo(string $newName): static;
}
