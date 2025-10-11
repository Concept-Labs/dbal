<?php
namespace Concept\DBAL\Expression;

use Concept\DBAL\Expression\Contract\AggregateFunctionsInterface;
use Concept\Expression\ExpressionInterface;

interface SqlExpressionInterface 
    extends 
        ExpressionInterface,
        AggregateFunctionsInterface
{
    const TYPE_NOTYPE = 'no-type';
    const TYPE_PIPE = 'pipe';
    const TYPE_SECTION = 'section';
    const TYPE_LIST = 'list';
    const TYPE_GROUP = 'group';
    const TYPE_IDENTIFIER = 'identifier';
    const TYPE_KEYWORD = 'keyword';
    const TYPE_VALUE = 'value';
    const TYPE_OPERATOR = 'operator';
    const TYPE_ALIAS = 'alias';
    const TYPE_CONDITION = 'condition';

    

    /**
     * Set the quote decorator
     * @todo: improve and make safer
     *
     * @param callable $quoteDecorator
     *
     * @return static
     */
    public function setQuoteDecorator(callable $quoteDecorator): static;

    /**
     * Generate a keyword expression
     * 
     * @param string $keyword
     * @return $this
     */
    public function keyword(string $keyword): static;

    /**
     * Generate an identifier expression
     * 
     * @param string $identifier
     * @return $this
     */
    public function identifier(string $identifier): static;

    /**
     * Add an alias to the query
     * 
     * @param string $alias The alias to add
     * @param string|SqlExpressionInterface $expression The expression to add
     * 
     * @return static
     */
    public function alias(
        string $alias, 
        string|SqlExpressionInterface $expression
    ): static;

    /**
     * Create a quoted value
     * 
     * @param mixed $value
     * @return $this
     */
    public function value(string $value): static;

    /**
     * Create a quoted value
     * 
     * @param string|null $value
     * @return $this
     */
    public function quote(?string $value = null): static;
    
    /**
     * Quote the items in the expression
     * 
     * @return $this
     */
    public function quoteItems(): static;

    /**
     * Generate a condition statement
     * 
     * @param string|SqlExpressionInterface $left
     * @param string $operator
     * @param mixed $right
     * 
     * @return static
     */
    public function condition(
        string|SqlExpressionInterface $left,
        string $operator, 
        array|string|int|float|bool|null|SqlExpressionInterface $right = null
        
    ): static;

    /**
     * Generate a IN statement
     *
     * @param string|SqlExpressionInterface $column
     * @param array|SqlExpressionInterface $values
     * 
     * @return static
     */
    public function in(
        string|SqlExpressionInterface $column, 
        array|SqlExpressionInterface $values
    ): static;

    /**
     * Generate a LIKE statement
     * 
     * @param string|SqlExpressionInterface $column
     * @param string|SqlExpressionInterface $value
     * 
     * @return static
     */
    public function like(
        string|SqlExpressionInterface $column, 
        string|SqlExpressionInterface $value
    ): static;

    /**
     * Generate a CASE statement
     * 
     * @param string|array|SqlExpressionInterface $condition
     * @param string|SqlExpressionInterface $thenValue
     * @param string|SqlExpressionInterface $elseValue
     * 
     * @return static
     */
    public function case(
        string|SqlExpressionInterface $condition,
        string|SqlExpressionInterface $thenValue,
        string|SqlExpressionInterface|null $elseValue = null
    ): static;
}