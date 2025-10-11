<?php
namespace Concept\DBAL\DDL\Builder;

use Concept\DBAL\DML\Builder\SqlBuilder;
use Concept\DBAL\Expression\SqlExpressionInterface;

class AlterTableBuilder extends SqlBuilder implements AlterTableBuilderInterface
{
    protected array $actions = [];
    protected string $table = '';

    public function alterTable(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    public function addColumn(string $name, string $type, array $options = []): static
    {
        $this->actions[] = [
            'type' => 'ADD COLUMN',
            'name' => $name,
            'definition' => $type,
            'options' => $options
        ];
        return $this;
    }

    public function modifyColumn(string $name, string $type, array $options = []): static
    {
        $this->actions[] = [
            'type' => 'MODIFY COLUMN',
            'name' => $name,
            'definition' => $type,
            'options' => $options
        ];
        return $this;
    }

    public function dropColumn(string $name): static
    {
        $this->actions[] = [
            'type' => 'DROP COLUMN',
            'name' => $name
        ];
        return $this;
    }

    public function renameColumn(string $oldName, string $newName): static
    {
        $this->actions[] = [
            'type' => 'RENAME COLUMN',
            'old_name' => $oldName,
            'new_name' => $newName
        ];
        return $this;
    }

    public function addConstraint(string $type, string|array $columns, array $options = []): static
    {
        $this->actions[] = [
            'type' => 'ADD CONSTRAINT',
            'constraint_type' => $type,
            'columns' => is_array($columns) ? $columns : [$columns],
            'options' => $options
        ];
        return $this;
    }

    public function dropConstraint(string $name): static
    {
        $this->actions[] = [
            'type' => 'DROP CONSTRAINT',
            'name' => $name
        ];
        return $this;
    }

    public function renameTo(string $newName): static
    {
        $this->actions[] = [
            'type' => 'RENAME TO',
            'new_name' => $newName
        ];
        return $this;
    }

    protected function getPipeline(): SqlExpressionInterface
    {
        $expr = $this->expression();
        
        // ALTER TABLE
        $expr->push($this->expression()->keyword('ALTER'))
            ->push($this->expression()->keyword('TABLE'))
            ->push($this->expression()->identifier($this->table));

        $actionExpressions = $this->expression()->join(', ');
        
        foreach ($this->actions as $action) {
            switch ($action['type']) {
                case 'ADD COLUMN':
                    $colDef = $this->expression()
                        ->push($this->expression()->keyword('ADD'))
                        ->push($this->expression()->keyword('COLUMN'))
                        ->push($this->expression()->identifier($action['name']))
                        ->push($action['definition']);
                    
                    foreach ($action['options'] as $key => $value) {
                        if (is_numeric($key)) {
                            $colDef->push($value);
                        } else {
                            $colDef->push($key)->push($value);
                        }
                    }
                    $actionExpressions->push($colDef->join(' '));
                    break;

                case 'MODIFY COLUMN':
                    $colDef = $this->expression()
                        ->push($this->expression()->keyword('MODIFY'))
                        ->push($this->expression()->keyword('COLUMN'))
                        ->push($this->expression()->identifier($action['name']))
                        ->push($action['definition']);
                    
                    foreach ($action['options'] as $key => $value) {
                        if (is_numeric($key)) {
                            $colDef->push($value);
                        } else {
                            $colDef->push($key)->push($value);
                        }
                    }
                    $actionExpressions->push($colDef->join(' '));
                    break;

                case 'DROP COLUMN':
                    $actionExpressions->push(
                        $this->expression()
                            ->push($this->expression()->keyword('DROP'))
                            ->push($this->expression()->keyword('COLUMN'))
                            ->push($this->expression()->identifier($action['name']))
                            ->join(' ')
                    );
                    break;

                case 'RENAME COLUMN':
                    $actionExpressions->push(
                        $this->expression()
                            ->push($this->expression()->keyword('RENAME'))
                            ->push($this->expression()->keyword('COLUMN'))
                            ->push($this->expression()->identifier($action['old_name']))
                            ->push($this->expression()->keyword('TO'))
                            ->push($this->expression()->identifier($action['new_name']))
                            ->join(' ')
                    );
                    break;

                case 'ADD CONSTRAINT':
                    $cols = $this->expression()->join(', ');
                    foreach ($action['columns'] as $col) {
                        $cols->push($this->expression()->identifier($col));
                    }
                    
                    $constraintExpr = $this->expression()
                        ->push($this->expression()->keyword('ADD'))
                        ->push($this->expression()->keyword($action['constraint_type']))
                        ->push($cols->wrap('(', ')'));
                    
                    if (!empty($action['options'])) {
                        foreach ($action['options'] as $key => $value) {
                            $constraintExpr->push($this->expression()->keyword($key))
                                ->push($value);
                        }
                    }
                    $actionExpressions->push($constraintExpr->join(' '));
                    break;

                case 'DROP CONSTRAINT':
                    $actionExpressions->push(
                        $this->expression()
                            ->push($this->expression()->keyword('DROP'))
                            ->push($this->expression()->keyword('CONSTRAINT'))
                            ->push($this->expression()->identifier($action['name']))
                            ->join(' ')
                    );
                    break;

                case 'RENAME TO':
                    $actionExpressions->push(
                        $this->expression()
                            ->push($this->expression()->keyword('RENAME'))
                            ->push($this->expression()->keyword('TO'))
                            ->push($this->expression()->identifier($action['new_name']))
                            ->join(' ')
                    );
                    break;
            }
        }

        $expr->push($actionExpressions);
        
        return $expr->join(' ');
    }
}
