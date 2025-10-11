<?php
namespace Concept\DBAL\DDL\Builder\Factory;

use Concept\DBAL\DDL\Builder\CreateTableBuilderInterface;

interface CreateTableBuilderFactoryInterface
{
    public function create(array $args = []): CreateTableBuilderInterface;
}
