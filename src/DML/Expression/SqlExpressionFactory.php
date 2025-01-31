<?php
namespace Concept\DBAL\DML\Expression;

use Concept\Di\Factory\Service\ServiceFactory;

class SqlExpressionFactory extends ServiceFactory implements SqlExpressionFactoryInterface
{
    public function create(...$args)
    {
        return $this->getFactory()->create(SqlExpressionInterface::class, ...$args);
    }
}