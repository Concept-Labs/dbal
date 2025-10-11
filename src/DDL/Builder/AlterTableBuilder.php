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
        // ALTER TABLE
        $expr = $this->expression(
            $this->expression()->keyword('ALTER'),
            $this->expression()->keyword('TABLE'),
            $this->expression()->identifier($this->table)
        );

        $actionExpressions = $this->expression()->join(', ');
        
        foreach ($this->actions as $action) {
            switch ($action['type']) {
                case 'ADD COLUMN':
                    $colDef = $this->expression(
                        $this->expression()->keyword('ADD'),
                        $this->expression()->keyword('COLUMN'),
                        $this->expression()->identifier($action['name']),
                        $action['definition']
                    );
                    
                    foreach ($action['options'] as $key => $value) {
                        if (is_numeric($key)) {
                            $colDef($value);
                        } else {
                            $colDef($key, $value);
                        }
                    }
                    $actionExpressions($colDef->join(' '));
                    break;

                case 'MODIFY COLUMN':
                    $colDef = $this->expression(
                        $this->expression()->keyword('MODIFY'),
                        $this->expression()->keyword('COLUMN'),
                        $this->expression()->identifier($action['name']),
                        $action['definition']
                    );
                    
                    foreach ($action['options'] as $key => $value) {
                        if (is_numeric($key)) {
                            $colDef($value);
                        } else {
                            $colDef($key, $value);
                        }
                    }
                    $actionExpressions($colDef->join(' '));
                    break;

                case 'DROP COLUMN':
                    $actionExpressions(
                        $this->expression(
                            $this->expression()->keyword('DROP'),
                            $this->expression()->keyword('COLUMN'),
                            $this->expression()->identifier($action['name'])
                        )->join(' ')
                    );
                    break;

                case 'RENAME COLUMN':
                    $actionExpressions(
                        $this->expression(
                            $this->expression()->keyword('RENAME'),
                            $this->expression()->keyword('COLUMN'),
                            $this->expression()->identifier($action['old_name']),
                            $this->expression()->keyword('TO'),
                            $this->expression()->identifier($action['new_name'])
                        )->join(' ')
                    );
                    break;

                case 'ADD CONSTRAINT':
                    $cols = $this->expression()->join(', ');
                    foreach ($action['columns'] as $col) {
                        $cols($this->expression()->identifier($col));
                    }
                    
                    $constraintExpr = $this->expression(
                        $this->expression()->keyword('ADD'),
                        $this->expression()->keyword($action['constraint_type']),
                        $cols->wrap('(', ')')
                    );
                    
                    if (!empty($action['options'])) {
                        foreach ($action['options'] as $key => $value) {
                            $constraintExpr(
                                $this->expression()->keyword($key),
                                $value
                            );
                        }
                    }
                    $actionExpressions($constraintExpr->join(' '));
                    break;

                case 'DROP CONSTRAINT':
                    $actionExpressions(
                        $this->expression(
                            $this->expression()->keyword('DROP'),
                            $this->expression()->keyword('CONSTRAINT'),
                            $this->expression()->identifier($action['name'])
                        )->join(' ')
                    );
                    break;

                case 'RENAME TO':
                    $actionExpressions(
                        $this->expression(
                            $this->expression()->keyword('RENAME'),
                            $this->expression()->keyword('TO'),
                            $this->expression()->identifier($action['new_name'])
                        )->join(' ')
                    );
                    break;
            }
        }

        $expr($actionExpressions);
        
        return $expr->join(' ');
    }
}
