<?php
namespace Concept\DBAL\DDL\Builder\Factory;

use Concept\DBAL\DDL\Builder\TruncateTableBuilderInterface;

interface TruncateTableBuilderFactoryInterface
{
    public function create(array $args = []): TruncateTableBuilderInterface;
}
