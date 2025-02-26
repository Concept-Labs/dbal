<?php
namespace Concept\DBAL\DML\Builder\Factory;

use Concept\DBAL\DML\Builder\InsertBuilderInterface;
use Concept\Singularity\Factory\ServiceFactory;

class InsertBuilderFactory extends ServiceFactory implements InsertBuilderFactoryInterface
{

    public function create(array $args = []): InsertBuilderInterface
    {
        return $this->createService(InsertBuilderInterface::class, $args);
    }
}