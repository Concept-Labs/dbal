<?php
namespace Concept\DBAL\DML\Builder\Factory;

use Concept\Di\Factory\Service\ServiceFactory;
use Concept\DBAL\DML\Builder\RawBuilderInterface;

class RawBuilderFactory extends ServiceFactory implements RawBuilderFactoryInterface
{

    public function create(...$args): RawBuilderInterface
    {
        return $this->getFactory()->create(RawBuilderInterface::class, ...$args);
    }
}