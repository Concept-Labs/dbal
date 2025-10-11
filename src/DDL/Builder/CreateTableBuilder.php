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
        $expr = $this->expression();
        
        // CREATE TABLE
        $expr->push($this->expression()->keyword('CREATE'))
            ->push($this->expression()->keyword('TABLE'));
        
        // IF NOT EXISTS
        if ($this->ifNotExists) {
            $expr->push($this->expression()->keyword('IF'))
                ->push($this->expression()->keyword('NOT'))
                ->push($this->expression()->keyword('EXISTS'));
        }
        
        // Table name
        $expr->push($this->expression()->identifier($this->table));
        
        // Column definitions
        $columnDefs = $this->expression()->join(', ');
        
        foreach ($this->columns as $name => $column) {
            $colDef = $this->expression()
                ->push($this->expression()->identifier($name))
                ->push($column['type']);
            
            foreach ($column['options'] as $key => $value) {
                if (is_numeric($key)) {
                    $colDef->push($value);
                } else {
                    $colDef->push($key)->push($value);
                }
            }
            
            $columnDefs->push($colDef->join(' '));
        }
        
        // Add primary key
        if ($this->primaryKey) {
            $pkCols = $this->expression()->join(', ');
            foreach ($this->primaryKey as $col) {
                $pkCols->push($this->expression()->identifier($col));
            }
            
            $columnDefs->push(
                $this->expression()
                    ->push($this->expression()->keyword('PRIMARY'))
                    ->push($this->expression()->keyword('KEY'))
                    ->push($pkCols->wrap('(', ')'))
                    ->join(' ')
            );
        }
        
        // Add unique constraints
        foreach ($this->uniqueConstraints as $unique) {
            $uniqCols = $this->expression()->join(', ');
            foreach ($unique as $col) {
                $uniqCols->push($this->expression()->identifier($col));
            }
            
            $columnDefs->push(
                $this->expression()
                    ->push($this->expression()->keyword('UNIQUE'))
                    ->push($uniqCols->wrap('(', ')'))
                    ->join(' ')
            );
        }
        
        // Add foreign keys
        foreach ($this->foreignKeys as $fk) {
            $fkExpr = $this->expression()
                ->push($this->expression()->keyword('FOREIGN'))
                ->push($this->expression()->keyword('KEY'))
                ->push(
                    $this->expression()
                        ->push($this->expression()->identifier($fk['column']))
                        ->wrap('(', ')')
                )
                ->push($this->expression()->keyword('REFERENCES'))
                ->push($this->expression()->identifier($fk['referenced_table']))
                ->push(
                    $this->expression()
                        ->push($this->expression()->identifier($fk['referenced_column']))
                        ->wrap('(', ')')
                );
            
            if (!empty($fk['options']['on_delete'])) {
                $fkExpr->push($this->expression()->keyword('ON'))
                    ->push($this->expression()->keyword('DELETE'))
                    ->push($this->expression()->keyword($fk['options']['on_delete']));
            }
            if (!empty($fk['options']['on_update'])) {
                $fkExpr->push($this->expression()->keyword('ON'))
                    ->push($this->expression()->keyword('UPDATE'))
                    ->push($this->expression()->keyword($fk['options']['on_update']));
            }
            
            $columnDefs->push($fkExpr->join(' '));
        }
        
        // Add indexes
        foreach ($this->indexes as $index) {
            $indexName = $index['name'] ?? 'idx_' . implode('_', $index['columns']);
            $indexCols = $this->expression()->join(', ');
            foreach ($index['columns'] as $col) {
                $indexCols->push($this->expression()->identifier($col));
            }
            
            $columnDefs->push(
                $this->expression()
                    ->push($this->expression()->keyword('INDEX'))
                    ->push($this->expression()->identifier($indexName))
                    ->push($indexCols->wrap('(', ')'))
                    ->join(' ')
            );
        }
        
        $expr->push($columnDefs->wrap('(', ')'));
        
        // Add table options
        if (!empty($this->tableOptions)) {
            foreach ($this->tableOptions as $key => $value) {
                $expr->push($this->expression()->push($key . '=' . $value));
            }
        }
        
        return $expr->join(' ');
    }
}
