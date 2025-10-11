<?php
namespace Concept\DBAL\DDL;

use Concept\Singularity\Factory\ServiceFactory;

class DdlManagerFactory extends ServiceFactory implements DdlManagerFactoryInterface
{
    /**
     * @param mixed ...$args
     * 
     * @return DdlManagerInterface
     */
    public function create(array $args = []): DdlManagerInterface
    {
        return $this->createService(DdlManagerInterface::class, $args);
    }
}
