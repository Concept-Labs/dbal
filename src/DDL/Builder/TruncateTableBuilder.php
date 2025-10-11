<?php
namespace Concept\DBAL\DDL\Builder;

use Concept\DBAL\DML\Builder\SqlBuilder;

class TruncateTableBuilder extends SqlBuilder implements TruncateTableBuilderInterface
{
    public function truncateTable(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    protected function buildQuery(): string
    {
        return 'TRUNCATE TABLE ' . $this->table;
    }
}
