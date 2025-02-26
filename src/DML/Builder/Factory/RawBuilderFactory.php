<?php
namespace Concept\DBAL\DML\Builder\Factory;


use Concept\DBAL\DML\Builder\RawBuilderInterface;
use Concept\Singularity\Factory\ServiceFactory;

class RawBuilderFactory extends ServiceFactory implements RawBuilderFactoryInterface
{

    public function create(array $args = []): RawBuilderInterface
    {
        return $this->createService(RawBuilderInterface::class, $args);
    }
}