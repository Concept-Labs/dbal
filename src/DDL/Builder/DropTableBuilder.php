<?php
namespace Concept\DBAL\DDL\Builder;

use Concept\DBAL\DML\Builder\SqlBuilder;
use Concept\DBAL\Expression\SqlExpressionInterface;

class DropTableBuilder extends SqlBuilder implements DropTableBuilderInterface
{
    protected bool $ifExists = false;
    protected ?string $cascadeOption = null;
    protected string $table = '';

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

    protected function getPipeline(): SqlExpressionInterface
    {
        $expr = $this->expression();
        
        // DROP TABLE
        $expr->push($this->expression()->keyword('DROP'))
            ->push($this->expression()->keyword('TABLE'));

        // IF EXISTS
        if ($this->ifExists) {
            $expr->push($this->expression()->keyword('IF'))
                ->push($this->expression()->keyword('EXISTS'));
        }

        // Table name
        $expr->push($this->expression()->identifier($this->table));

        // CASCADE or RESTRICT
        if ($this->cascadeOption) {
            $expr->push($this->expression()->keyword($this->cascadeOption));
        }

        return $expr->join(' ');
    }
}
