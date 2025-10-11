<?php
namespace Concept\DBAL\DDL\Builder\Factory;

use Concept\DBAL\DDL\Builder\DropTableBuilderInterface;

interface DropTableBuilderFactoryInterface
{
    public function create(array $args = []): DropTableBuilderInterface;
}
