<?php
namespace Concept\DBAL\DDL\Builder;

use Concept\DBAL\DML\Builder\SqlBuilder;
use Concept\DBAL\Expression\SqlExpressionInterface;

class TruncateTableBuilder extends SqlBuilder implements TruncateTableBuilderInterface
{
    protected string $table = '';
    
    public function truncateTable(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    protected function getPipeline(): SqlExpressionInterface
    {
        return $this->expression(
            $this->expression()->keyword('TRUNCATE'),
            $this->expression()->keyword('TABLE'),
            $this->expression()->identifier($this->table)
        )->join(' ');
    }
}
