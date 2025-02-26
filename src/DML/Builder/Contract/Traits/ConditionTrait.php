<?php
namespace Concept\DBAL\DML\Builder\Contract\Traits;

use Concept\DBAL\DML\Expression\KeywordEnum;
use Concept\DBAL\DML\Expression\SqlExpressionInterface;
use Stringable;

trait ConditionTrait
{
    public function where(string|SqlExpressionInterface ...$expressions): static
    {    
        return $this->addConditionToSection(
            KeywordEnum::WHERE,
            KeywordEnum::AND,
            ...$expressions
            //...array_map(fn($expr) => $expr instanceof SqlExpressionInterface ? $expr : $this->value($expr), $expressions)
        );
    }

    /**
     * Add an OR WHERE to the query
     * 
     * @param string|Stringable|SqlExpressionInterface ...$expressions The expressions to add
     * 
     * @return static
     */
    public function orWhere(...$expressions): static
    {    
        return $this->addConditionToSection(
            KeywordEnum::WHERE,
            KeywordEnum::OR,
            ...$expressions
        );
    }

    public function whereIn(
        string|SqlExpressionInterface $column, 
        array|SqlExpressionInterface $values
    ): static
    {
        return $this->where(
            $this->expression()->in($column, $values)
        );
    }

    public function whereLike(
        string|SqlExpressionInterface $column, 
        string|SqlExpressionInterface $value
    ): static
    {
        return $this->where(
            $this->expression()->like($column, $value)
        );
    }

    /**
     * @todo Add support for subqueries
     * {@inheritDoc}
     */
    public function whereCase(
        string|array|SqlExpressionInterface $condition,
        string|SqlExpressionInterface $thenValue,
        string|SqlExpressionInterface|null $elseValue = null
    ): static
    {
        return $this->where(
            $this->expression()->case($condition, $thenValue, $elseValue)
        );
    }

    /**
     * Add a HAVING to the query
     * 
     * @param string|Stringable|SqlExpressionInterface ...$expressions The expressions to add
     * 
     * @return static
     */
    public function having(...$expressions): static
    {
        return $this->addConditionToSection(
            KeywordEnum::HAVING,
            KeywordEnum::AND,
            ...$expressions
        );
    }

    /**
     * Add an OR HAVING to the query
     * 
     * @param string|Stringable|SqlExpressionInterface ...$expressions The expressions to add
     * 
     * @return static
     */
    public function orHaving(...$expressions): static
    {
        return $this->addConditionToSection(
            KeywordEnum::HAVING,
            KeywordEnum::OR, 
            ...$expressions
        );
    }

    public function havingLike(string|SqlExpressionInterface $column, string|SqlExpressionInterface $value): static
    {
        return $this->having(
            $this->expression()->like($column, $value)
        );
    }

    public function havingIn(
        string|SqlExpressionInterface $column, 
        array|SqlExpressionInterface $values
    ): static
    {
        return $this->having(
            $this->expression()->in($column, $values)
        );
    }

    public function havingCase(
        string|array|SqlExpressionInterface $condition,
        string|SqlExpressionInterface $thenValue,
        string|SqlExpressionInterface|null $elseValue = null
    ): static
    {
        return $this->having(
            $this->expression()->case($condition, $thenValue, $elseValue)
        );
    }

    public function between(): static
    {
        return $this;
    }
    
    /**
     * Aggregate conditions
     * 
     * @param mixed ...$expressions The expressions
     * 
     * @return static
     */
    protected function aggregateConditions(...$expressions): SqlExpressionInterface
    {
        return $this->expression(
            ...$expressions
        )
            ->join($this->expression()->keyword(KeywordEnum::AND))
            ->wrap('(', ')')
            ->type(SqlExpressionInterface::TYPE_CONDITION)
        ;
    }

    /**
     * Add a condition to the section
     * 
     * @param string $section       The section to use: WHERE or HAVING
     * @param string $left          The left expression to use: AND or OR
     * @param mixed ...$expressions The expressions to add
     * 
     * @return static
     */
    protected function addConditionToSection(string $section, string $left, ...$expressions): static
    {
        $this->getSection($section)
            ->push(
                !$this->getSection($section)->isEmpty() ? $this->expression()->keyword($left) : null,
                $this->aggregateConditions(...$expressions)
            );

        return $this;
    }

    

    

    

}