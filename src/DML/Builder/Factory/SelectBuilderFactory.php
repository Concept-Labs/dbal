<?php
namespace Concept\DBAL\DML\Builder\Factory;

use Concept\DBAL\DML\Builder\SelectBuilderInterface;
use Concept\Di\Factory\Service\ServiceFactory;

class SelectBuilderFactory extends ServiceFactory
{

    public function create(...$args): SelectBuilderInterface
    {
        return $this->getFactory()
            ->create(SelectBuilderInterface::class, ...$args);

        return $object;
    }
}