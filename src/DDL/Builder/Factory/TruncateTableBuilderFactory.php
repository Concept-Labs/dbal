<?php
namespace Concept\DBAL\DDL\Builder\Factory;

use Concept\DBAL\DDL\Builder\TruncateTableBuilderInterface;
use Concept\Singularity\Factory\ServiceFactory;

class TruncateTableBuilderFactory extends ServiceFactory implements TruncateTableBuilderFactoryInterface
{
    public function create(array $args = []): TruncateTableBuilderInterface
    {
        return $this->createService(TruncateTableBuilderInterface::class, $args);
    }
}
