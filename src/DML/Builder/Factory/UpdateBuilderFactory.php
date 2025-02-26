<?php
namespace Concept\DBAL\DML\Builder\Factory;

use Concept\DBAL\DML\Builder\UpdateBuilderInterface;
use Concept\Singularity\Factory\ServiceFactory;

class UpdateBuilderFactory extends ServiceFactory implements UpdateBuilderFactoryInterface
{

    public function create(array $args = []): UpdateBuilderInterface
    {
        return $this->createService(UpdateBuilderInterface::class, $args);        
    }
}