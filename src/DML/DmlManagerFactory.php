<?php
namespace Concept\DBAL\DML;

use Concept\Singularity\Factory\ServiceFactory;

class DmlManagerFactory extends ServiceFactory implements DmlManagerFactoryInterface
{
    /**
     * @param mixed ...$args
     * 
     * @return DmlManagerInterface
     */
    public function create(array $args = []): DmlManagerInterface
    {
        return $this->createService(DmlManagerInterface::class, $args);
    }
}