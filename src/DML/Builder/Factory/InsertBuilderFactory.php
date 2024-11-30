<?php
namespace Concept\DBAL\DML\Builder\Factory;

use Concept\Di\Factory\Service\ServiceFactory;
use Concept\DBAL\DML\Builder\InsertBuilderInterface;

class InsertBuilderFactory extends ServiceFactory
{

    public function create(...$args): InsertBuilderInterface
    {
        return $this->getFactory()->create(InsertBuilderInterface::class, ...$args);
    }
}