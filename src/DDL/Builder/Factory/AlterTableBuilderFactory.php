<?php
namespace Concept\DBAL\DDL\Builder\Factory;

use Concept\DBAL\DDL\Builder\AlterTableBuilderInterface;
use Concept\Singularity\Factory\ServiceFactory;

class AlterTableBuilderFactory extends ServiceFactory implements AlterTableBuilderFactoryInterface
{
    public function create(array $args = []): AlterTableBuilderInterface
    {
        return $this->createService(AlterTableBuilderInterface::class, $args);
    }
}
