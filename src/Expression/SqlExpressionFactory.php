<?php
namespace Concept\DBAL\Expression;

use Concept\Singularity\Factory\ServiceFactory;

class SqlExpressionFactory extends ServiceFactory implements SqlExpressionFactoryInterface
{
    public function create(array $args = [])
    {
        return $this->createService(SqlExpressionInterface::class, $args);
    }
}