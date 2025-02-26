<?php
namespace Concept\DBAL\DML\Builder\Factory;

use Concept\DBAL\DML\Builder\SelectBuilderInterface;
use Concept\Singularity\Factory\ServiceFactory;

class SelectBuilderFactory extends ServiceFactory implements SelectBuilderFactoryInterface
{

    public function create(array $args = []): SelectBuilderInterface
    {
        return $this->createService(SelectBuilderInterface::class, $args);
    }
}