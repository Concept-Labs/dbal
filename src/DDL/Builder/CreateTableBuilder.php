<?php
namespace Concept\DBAL\DDL\Builder;

use Concept\DBAL\DML\Builder\SqlBuilder;
use Concept\DBAL\Expression\KeywordEnum;
use Concept\DBAL\Expression\SqlExpressionInterface;

class CreateTableBuilder extends SqlBuilder implements CreateTableBuilderInterface
{
    protected array $columns = [];
    protected ?array $primaryKey = null;
    protected array $foreignKeys = [];
    protected array $uniqueConstraints = [];
    protected array $indexes = [];
    protected array $tableOptions = [];
    protected bool $ifNotExists = false;
    protected string $table = '';

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

    protected function getPipeline(): SqlExpressionInterface
    {
        $expr = $this->expression(
            $this->expression()->keyword('CREATE'),
            $this->expression()->keyword('TABLE')
        );
        
        // IF NOT EXISTS
        if ($this->ifNotExists) {
            $expr(
                $this->expression()->keyword('IF'),
                $this->expression()->keyword('NOT'),
                $this->expression()->keyword('EXISTS')
            );
        }
        
        // Table name
        $expr($this->expression()->identifier($this->table));
        
        // Column definitions
        $columnDefs = $this->expression()->join(', ');
        
        foreach ($this->columns as $name => $column) {
            $colDef = $this->expression(
                $this->expression()->identifier($name),
                $column['type']
            );
            
            foreach ($column['options'] as $key => $value) {
                if (is_numeric($key)) {
                    $colDef($value);
                } else {
                    $colDef($key, $value);
                }
            }
            
            $columnDefs($colDef->join(' '));
        }
        
        // Add primary key
        if ($this->primaryKey) {
            $pkCols = $this->expression()->join(', ');
            foreach ($this->primaryKey as $col) {
                $pkCols($this->expression()->identifier($col));
            }
            
            $columnDefs(
                $this->expression(
                    $this->expression()->keyword('PRIMARY'),
                    $this->expression()->keyword('KEY'),
                    $pkCols->wrap('(', ')')
                )->join(' ')
            );
        }
        
        // Add unique constraints
        foreach ($this->uniqueConstraints as $unique) {
            $uniqCols = $this->expression()->join(', ');
            foreach ($unique as $col) {
                $uniqCols($this->expression()->identifier($col));
            }
            
            $columnDefs(
                $this->expression(
                    $this->expression()->keyword('UNIQUE'),
                    $uniqCols->wrap('(', ')')
                )->join(' ')
            );
        }
        
        // Add foreign keys
        foreach ($this->foreignKeys as $fk) {
            $fkExpr = $this->expression(
                $this->expression()->keyword('FOREIGN'),
                $this->expression()->keyword('KEY'),
                $this->expression($this->expression()->identifier($fk['column']))->wrap('(', ')'),
                $this->expression()->keyword('REFERENCES'),
                $this->expression()->identifier($fk['referenced_table']),
                $this->expression($this->expression()->identifier($fk['referenced_column']))->wrap('(', ')')
            );
            
            if (!empty($fk['options']['on_delete'])) {
                $fkExpr(
                    $this->expression()->keyword('ON'),
                    $this->expression()->keyword('DELETE'),
                    $this->expression()->keyword($fk['options']['on_delete'])
                );
            }
            if (!empty($fk['options']['on_update'])) {
                $fkExpr(
                    $this->expression()->keyword('ON'),
                    $this->expression()->keyword('UPDATE'),
                    $this->expression()->keyword($fk['options']['on_update'])
                );
            }
            
            $columnDefs($fkExpr->join(' '));
        }
        
        // Add indexes
        foreach ($this->indexes as $index) {
            $indexName = $index['name'] ?? 'idx_' . implode('_', $index['columns']);
            $indexCols = $this->expression()->join(', ');
            foreach ($index['columns'] as $col) {
                $indexCols($this->expression()->identifier($col));
            }
            
            $columnDefs(
                $this->expression(
                    $this->expression()->keyword('INDEX'),
                    $this->expression()->identifier($indexName),
                    $indexCols->wrap('(', ')')
                )->join(' ')
            );
        }
        
        $expr($columnDefs->wrap('(', ')'));
        
        // Add table options
        if (!empty($this->tableOptions)) {
            foreach ($this->tableOptions as $key => $value) {
                $expr($this->expression($key . '=' . $value));
            }
        }
        
        return $expr->join(' ');
    }
}
