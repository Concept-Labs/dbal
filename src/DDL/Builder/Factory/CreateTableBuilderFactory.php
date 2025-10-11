<?php
namespace Concept\DBAL\DDL\Builder\Factory;

use Concept\DBAL\DDL\Builder\CreateTableBuilderInterface;
use Concept\Singularity\Factory\ServiceFactory;

class CreateTableBuilderFactory extends ServiceFactory implements CreateTableBuilderFactoryInterface
{
    public function create(array $args = []): CreateTableBuilderInterface
    {
        return $this->createService(CreateTableBuilderInterface::class, $args);
    }
}
