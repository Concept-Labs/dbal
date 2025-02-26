<?php
namespace Concept\DBAL\DML\Expression\Contract;

use Concept\DBAL\DML\Expression\SqlExpressionInterface;
use Concept\DBAL\Exception\RuntimeException;

trait SqlExpressionAwareTrait
{
    //private ?SqlExpressionInterface $sqlExpressionPrototype = null;

    /**
     * Set the expression prototype
     * 
     * @param SqlExpressionInterface $sqlExpressionPrototype The prototype
     * 
     * @return void
     */
    public function setSqlExpressionPrototype(SqlExpressionInterface $sqlExpressionPrototype): void
    {
        $this->sqlExpressionPrototype = $sqlExpressionPrototype;
    }
    

    /**
     * Get the expression prototype
     * 
     * @return SqlExpressionInterface
     */
    protected function getExpressionPrototype(): SqlExpressionInterface
    {
        return clone (
            $this->sqlExpressionPrototype 
            ?? throw new RuntimeException('The expression prototype is not set.')
        );
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

    public function sql(...$expressions): SqlExpressionInterface
    {
        return $this->expression(...$expressions);
    }
}