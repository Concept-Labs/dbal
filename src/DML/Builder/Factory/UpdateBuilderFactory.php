<?php
namespace Concept\DBAL\DML\Builder\Factory;

use Concept\DBAL\DML\Builder\UpdateBuilderInterface;
use Concept\Di\Factory\Service\ServiceFactory;

class UpdateBuilderFactory extends ServiceFactory implements UpdateBuilderFactoryInterface
{

    public function create(...$args): UpdateBuilderInterface
    {
        return $this->getFactory()->create(UpdateBuilderInterface::class, ...$args);
    }
}