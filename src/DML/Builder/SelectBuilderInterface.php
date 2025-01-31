<?php
namespace Concept\DBAL\DML\Builder;

use Concept\DBAL\DML\Builder\Contract\ConditionableInterface;
use Concept\DBAL\DML\Builder\Contract\ExplainableInterface;
use Concept\DBAL\DML\Builder\Contract\GroupableInterface;
use Concept\DBAL\DML\Builder\Contract\JoinableInterface;
use Concept\DBAL\DML\Builder\Contract\LimitableInterface;
use Concept\DBAL\DML\Builder\Contract\LockableInterface;
use Concept\DBAL\DML\Builder\Contract\OrderableInterface;
use Concept\DBAL\DML\Builder\Contract\SelectableInterface;
use Concept\DBAL\DML\Builder\Contract\UnionableInterface;

interface SelectBuilderInterface 
    extends 
        SqlBuilderInterface,
        SelectableInterface,
        ConditionableInterface,
        JoinableInterface,
        OrderableInterface,
        GroupableInterface,
        UnionableInterface,
        LockableInterface,
        ExplainableInterface,
        LimitableInterface
{

    public function describe(string $table): static;
    
}