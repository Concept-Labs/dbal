<?php
namespace Concept\DBAL\DDL\Builder\Factory;

use Concept\DBAL\DDL\Builder\AlterTableBuilderInterface;

interface AlterTableBuilderFactoryInterface
{
    public function create(array $args = []): AlterTableBuilderInterface;
}
