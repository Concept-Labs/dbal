<?php
namespace Concept\DBAL\DDL\Builder;

use Concept\DBAL\DML\Builder\SqlBuilder;

class CreateTableBuilder extends SqlBuilder implements CreateTableBuilderInterface
{
    protected array $columns = [];
    protected ?array $primaryKey = null;
    protected array $foreignKeys = [];
    protected array $uniqueConstraints = [];
    protected array $indexes = [];
    protected array $tableOptions = [];
    protected bool $ifNotExists = false;

    public function createTable(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    public function ifNotExists(): static
    {
        $this->ifNotExists = true;
        return $this;
    }

    public function addColumn(string $name, string $type, array $options = []): static
    {
        $this->columns[$name] = ['type' => $type, 'options' => $options];
        return $this;
    }

    public function primaryKey(string|array $columns): static
    {
        $this->primaryKey = is_array($columns) ? $columns : [$columns];
        return $this;
    }

    public function foreignKey(string $column, string $referencedTable, string $referencedColumn, array $options = []): static
    {
        $this->foreignKeys[] = [
            'column' => $column,
            'referenced_table' => $referencedTable,
            'referenced_column' => $referencedColumn,
            'options' => $options
        ];
        return $this;
    }

    public function unique(string|array $columns): static
    {
        $this->uniqueConstraints[] = is_array($columns) ? $columns : [$columns];
        return $this;
    }

    public function index(string|array $columns, ?string $name = null): static
    {
        $this->indexes[] = [
            'columns' => is_array($columns) ? $columns : [$columns],
            'name' => $name
        ];
        return $this;
    }

    public function options(array $options): static
    {
        $this->tableOptions = $options;
        return $this;
    }

    protected function buildQuery(): string
    {
        $parts = ['CREATE TABLE'];

        if ($this->ifNotExists) {
            $parts[] = 'IF NOT EXISTS';
        }

        $parts[] = $this->table;

        // Build column definitions
        $columnDefs = [];
        foreach ($this->columns as $name => $column) {
            $def = $name . ' ' . $column['type'];
            foreach ($column['options'] as $key => $value) {
                if (is_numeric($key)) {
                    $def .= ' ' . $value;
                } else {
                    $def .= ' ' . $key . ' ' . $value;
                }
            }
            $columnDefs[] = $def;
        }

        // Add primary key
        if ($this->primaryKey) {
            $columnDefs[] = 'PRIMARY KEY (' . implode(', ', $this->primaryKey) . ')';
        }

        // Add unique constraints
        foreach ($this->uniqueConstraints as $unique) {
            $columnDefs[] = 'UNIQUE (' . implode(', ', $unique) . ')';
        }

        // Add foreign keys
        foreach ($this->foreignKeys as $fk) {
            $def = 'FOREIGN KEY (' . $fk['column'] . ') REFERENCES ' . 
                   $fk['referenced_table'] . ' (' . $fk['referenced_column'] . ')';
            
            if (!empty($fk['options']['on_delete'])) {
                $def .= ' ON DELETE ' . $fk['options']['on_delete'];
            }
            if (!empty($fk['options']['on_update'])) {
                $def .= ' ON UPDATE ' . $fk['options']['on_update'];
            }
            
            $columnDefs[] = $def;
        }

        // Add indexes
        foreach ($this->indexes as $index) {
            $indexName = $index['name'] ?? 'idx_' . implode('_', $index['columns']);
            $columnDefs[] = 'INDEX ' . $indexName . ' (' . implode(', ', $index['columns']) . ')';
        }

        $parts[] = '(' . implode(', ', $columnDefs) . ')';

        // Add table options
        if (!empty($this->tableOptions)) {
            foreach ($this->tableOptions as $key => $value) {
                $parts[] = $key . '=' . $value;
            }
        }

        return implode(' ', $parts);
    }
}
