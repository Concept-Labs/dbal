<?php
namespace Concept\DBAL\DML\Builder\Factory;

use Concept\DBAL\DML\Builder\DeleteBuilderInterface;
use Concept\Singularity\Factory\ServiceFactory;

class DeleteBuilderFactory extends ServiceFactory implements DeleteBuilderFactoryInterface
{

    public function create(array $args = []): DeleteBuilderInterface
    {
        return $this->createService(DeleteBuilderInterface::class, $args);
    }
}