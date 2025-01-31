<?php
namespace Concept\DBAL\DML\Builder\Factory;

use Concept\DBAL\DML\Builder\DeleteBuilderInterface;
use Concept\Di\Factory\Service\ServiceFactory;

class DeleteBuilderFactory extends ServiceFactory implements DeleteBuilderFactoryInterface
{

    public function create(...$args): DeleteBuilderInterface
    {
        return $this->getFactory()->create(DeleteBuilderInterface::class, ...$args);
    }
}