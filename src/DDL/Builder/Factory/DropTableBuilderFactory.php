<?php
namespace Concept\DBAL\DDL\Builder\Factory;

use Concept\DBAL\DDL\Builder\DropTableBuilderInterface;
use Concept\Singularity\Factory\ServiceFactory;

class DropTableBuilderFactory extends ServiceFactory implements DropTableBuilderFactoryInterface
{
    public function create(array $args = []): DropTableBuilderInterface
    {
        return $this->createService(DropTableBuilderInterface::class, $args);
    }
}
