<?php
namespace Concept\DBAL\DML\Expression\Contract;

use Concept\DBAL\DML\Expression\SqlExpressionFactoryInterface;
use Concept\DBAL\DML\Expression\SqlExpressionInterface;
use Concept\DBAL\Exception\RuntimeException;
use Concept\DBC\Contract\ConnectionAwareInterface;
use Concept\DI\Factory\Attribute\Injector;

trait SqlExpressionAwareTrait
{
    private ?SqlExpressionInterface $sqlExpressionPrototype = null;
    private ?SqlExpressionFactoryInterface $sqlExpressionFactory = null;

    /**
     * Inject the expression factory
     * 
     * @param SqlExpressionFactoryInterface $expressionFactory The expression factory
     * 
     * @return void
     */
    #[Injector]
    public function withSqlExpressionFactory(SqlExpressionFactoryInterface $sqlExpressionFactory): void
    {
        $this->sqlExpressionFactory = $sqlExpressionFactory;
    }

    /**
     * Get the expression factory
     * 
     * @return SqlExpressionFactoryInterface
     */
    protected function getSqlExpressionFactory(): SqlExpressionFactoryInterface
    {
        if (null === $this->sqlExpressionFactory) {
            throw new RuntimeException('The expression factory is not set');
        }

        return $this->sqlExpressionFactory;
    }
    

    /**
     * Get the expression prototype
     * 
     * @return SqlExpressionInterface
     */
    protected function getExpressionPrototype(): SqlExpressionInterface
    {
        if (null === $this->sqlExpressionPrototype) {
            $this->sqlExpressionPrototype = $this->getSqlExpressionFactory()->create();
        }
$d = $this->getConnection()->getDriver();
if (null === $d) {
    $test = 1;
}
        if ($this instanceof ConnectionAwareInterface && null !== $this->getConnection()) {
            $this->sqlExpressionPrototype->setQuoteDecorator(
                fn($value) => $this->getConnection()->getDriver()->quote($value)
            );
        }            

        return $this->sqlExpressionPrototype->prototype();
    }

    /**
     * Create a new expression
     * 
     * @param mixed ...$expressions The expressions to add
     * 
     * @return SqlExpressionInterface
     */
    public function expression(...$expressions): SqlExpressionInterface
    {
        $expression =  $this->getExpressionPrototype();
        
        if (!empty($expressions)) {
            $expression->push(...$expressions);
        }

        return $expression;
    }

    // public function expr(...$expressions): SqlExpressionInterface
    // {
    //     return $this->expression(...$expressions);
    // }
}