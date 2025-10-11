<?php
namespace Concept\DBAL\DDL\Builder;

use Concept\DBAL\DML\Builder\SqlBuilder;

class AlterTableBuilder extends SqlBuilder implements AlterTableBuilderInterface
{
    protected array $actions = [];

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

    protected function buildQuery(): string
    {
        $parts = ['ALTER TABLE', $this->table];

        $actionStrings = [];
        foreach ($this->actions as $action) {
            switch ($action['type']) {
                case 'ADD COLUMN':
                    $def = $action['type'] . ' ' . $action['name'] . ' ' . $action['definition'];
                    foreach ($action['options'] as $key => $value) {
                        if (is_numeric($key)) {
                            $def .= ' ' . $value;
                        } else {
                            $def .= ' ' . $key . ' ' . $value;
                        }
                    }
                    $actionStrings[] = $def;
                    break;

                case 'MODIFY COLUMN':
                    $def = $action['type'] . ' ' . $action['name'] . ' ' . $action['definition'];
                    foreach ($action['options'] as $key => $value) {
                        if (is_numeric($key)) {
                            $def .= ' ' . $value;
                        } else {
                            $def .= ' ' . $key . ' ' . $value;
                        }
                    }
                    $actionStrings[] = $def;
                    break;

                case 'DROP COLUMN':
                    $actionStrings[] = $action['type'] . ' ' . $action['name'];
                    break;

                case 'RENAME COLUMN':
                    $actionStrings[] = $action['type'] . ' ' . $action['old_name'] . ' TO ' . $action['new_name'];
                    break;

                case 'ADD CONSTRAINT':
                    $def = 'ADD ' . $action['constraint_type'] . ' (' . implode(', ', $action['columns']) . ')';
                    if (!empty($action['options'])) {
                        foreach ($action['options'] as $key => $value) {
                            $def .= ' ' . $key . ' ' . $value;
                        }
                    }
                    $actionStrings[] = $def;
                    break;

                case 'DROP CONSTRAINT':
                    $actionStrings[] = $action['type'] . ' ' . $action['name'];
                    break;

                case 'RENAME TO':
                    $actionStrings[] = $action['type'] . ' ' . $action['new_name'];
                    break;
            }
        }

        $parts[] = implode(', ', $actionStrings);

        return implode(' ', $parts);
    }
}
