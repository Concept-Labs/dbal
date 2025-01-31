<?php
namespace Concept\DBAL\DML;

use Concept\Di\Factory\Service\ServiceFactory;

class DmlManagerFactory extends ServiceFactory
{
    /**
     * @param mixed ...$args
     * 
     * @return DmlManagerInterface
     */
    public function create(...$args): DmlManagerInterface
    {
        return $this->getFactory()->create(DmlManagerInterface::class, ...$args);
    }
}