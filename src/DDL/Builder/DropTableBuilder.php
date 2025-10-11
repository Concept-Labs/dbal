<?php
namespace Concept\DBAL\DDL\Builder;

use Concept\DBAL\DML\Builder\SqlBuilder;

class DropTableBuilder extends SqlBuilder implements DropTableBuilderInterface
{
    protected bool $ifExists = false;
    protected ?string $cascadeOption = null;

    public function dropTable(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    public function ifExists(): static
    {
        $this->ifExists = true;
        return $this;
    }

    public function cascade(): static
    {
        $this->cascadeOption = 'CASCADE';
        return $this;
    }

    public function restrict(): static
    {
        $this->cascadeOption = 'RESTRICT';
        return $this;
    }

    protected function buildQuery(): string
    {
        $parts = ['DROP TABLE'];

        if ($this->ifExists) {
            $parts[] = 'IF EXISTS';
        }

        $parts[] = $this->table;

        if ($this->cascadeOption) {
            $parts[] = $this->cascadeOption;
        }

        return implode(' ', $parts);
    }
}
