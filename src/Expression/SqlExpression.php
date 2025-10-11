<?php

namespace Concept\DBAL\Expression;

use Concept\DBAL\Expression\Contract\AggregateFunctionsTrait;
use Concept\DBAL\Expression\Dialect\SqlDialectInterface;
use Concept\DBAL\Expression\Dialect\MySqlDialect;
use Concept\Expression\Expression;
use Concept\DBAL\Exception\RuntimeException;

class SqlExpression extends Expression implements SqlExpressionInterface
{

    use AggregateFunctionsTrait;

    private $quoteDecorator = null;
    private ?SqlDialectInterface $dialect = null;

    /**
     * Set the SQL dialect
     * 
     * @param SqlDialectInterface $dialect The SQL dialect
     * @return static
     */
    public function setDialect(SqlDialectInterface $dialect): static
    {
        $this->dialect = $dialect;
        return $this;
    }

    /**
     * Get the SQL dialect
     * 
     * @return SqlDialectInterface
     */
    protected function getDialect(): SqlDialectInterface
    {
        if (null === $this->dialect) {
            // Default to MySQL dialect for backward compatibility
            $this->dialect = new MySqlDialect();
        }
        return $this->dialect;
    }

    
    public function setQuoteDecorator(callable $quoteDecorator): static
    {
        $this->quoteDecorator = $quoteDecorator;

        return $this;
    }

    protected function getQuoteDecorator(): callable
    {
        if (null === $this->quoteDecorator) {
            // Use dialect for quoting if available
            $dialect = $this->getDialect();
            $this->quoteDecorator = fn($value) => $dialect->quoteValue($value);
        }
        return $this->quoteDecorator;
    }

    /**
     * {@inheritDoc}
     */
    public function quoteItems(): static
    {
        $this->decorateItem(
            $this->getQuoteDecorator()
        );
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function value($value): static
    {
        return $this->quote($value)
            ->type(SqlExpressionInterface::TYPE_VALUE);
    }

    /**
     * {@inheritDoc}
     */
    public function quote(?string $value = null): static
    {
        $expr = null === $value ? $this : $this->prototype()->push($value);
        
        return $expr
            ->decorate(
                $this->getQuoteDecorator()
            );
    }

    


    /**
     * {@inheritDoc}
     */
    public function keyword(string $keyword): static
    {
        /**
         * @todo Use the keyword enum
         */
        // try {
        //     $keyword = KeywordEnum::{$keyword};
        // } catch (\Throwable $e) {
        //     throw new InvalidArgumentException("Invalid keyword: $keyword");
        // }

        return $this->prototype()
            ->push($keyword)
            ->type(SqlExpressionInterface::TYPE_KEYWORD);
    }

    /**
     * {@inheritDoc}
     */
    public function identifier(string $identifier): static
    {
        return $this->prototype()
            ->push($identifier)
            ->decorate(
                fn($value) => $this->quoteIdentifier($value)
            )
            ->type(SqlExpressionInterface::TYPE_IDENTIFIER)
            ;
    }

    public function operator(string $operator): static
    {
        if (
            /**
             * @todo Use the operator enum?
             */
            !in_array(
                $operator, 
                ['=', '<', '>', '<=', '>=', '<>', '!=', 'LIKE', 'IN', 'IS', 'IS NOT']
            )
        ) {
            throw new RuntimeException("Invalid operator: $operator");
        }

        return $this->prototype()
            ->push($operator)
            ->type(SqlExpressionInterface::TYPE_OPERATOR);
    }

    /**
     * {@inheritDoc}
     */
    public function alias(string $alias, string|SqlExpressionInterface $expression): static
    {
        return $this->prototype()->push(
            $expression instanceof SqlExpressionInterface
                ? $expression->wrap('(', ')')
                : $this->identifier($expression),
            $this->keyword(KeywordEnum::AS),
            $this->identifier($alias)
        )->type(SqlExpressionInterface::TYPE_ALIAS);
    }

    /**
     * {@inheritDoc}
     */
    public function condition(
        string|SqlExpressionInterface $left,
        string $operator, 
        array|string|int|float|bool|null|SqlExpressionInterface $right = null
        
    ): static
    {
        return $this->prototype()->push(
            match(true) {
                $left instanceof SqlExpressionInterface => $left->wrap('(', ')'),
                //is_array($left) => $this->alias...
                default => $this->identifier($left)
            },
            $this->operator($operator),
            match (true) {
                null === $right => $this->keyword(KeywordEnum::NULL),
                $right instanceof SqlExpressionInterface => $right,
                is_numeric($right) => $right,
                is_array($right) => $this->push(...$right)->join(',')->wrap('(', ')')->quoteItems(),
                default => $this->value($right)
            }
        )->type(SqlExpressionInterface::TYPE_CONDITION);
    }

    /**
     * {@inheritDoc}
     */
    public function in(string|SqlExpressionInterface $column, array|SqlExpressionInterface $values): static
    {
        return $this->condition(
            $column,
            KeywordEnum::IN,
            $values
        );
    }

    /**
     * {@inheritDoc}
     */
    public function like(string|SqlExpressionInterface $column, string|SqlExpressionInterface $value): static
    {
        return $this->condition(
            $column,
            KeywordEnum::LIKE,
            $value
        );
    }

    /**
     * {@inheritDoc}
     */
    public function case(
        string|SqlExpressionInterface $condition,
        string|SqlExpressionInterface $thenValue,
        string|SqlExpressionInterface|null $elseValue = null): static
    {
        $caseExpression = $this->prototype()
            ->wrap(' ')
            ->wrap(KeywordEnum::CASE, KeywordEnum::END)
            ->push(
                $this->keyword(KeywordEnum::WHEN),
                match (true) {
                    $condition instanceof SqlExpressionInterface => $condition->wrap('(', ')'),
                    default => $condition
                },
                $this->keyword(KeywordEnum::THEN),
                match(true) {
                    $thenValue instanceof SqlExpressionInterface => $thenValue->wrap('(', ')'),
                    default => $this->value($thenValue)
                }
            );

        if ($elseValue !== null) {
            $caseExpression->push(
                $this->keyword(KeywordEnum::ELSE), 
                match(true) {
                    $elseValue instanceof SqlExpressionInterface => $elseValue->wrap('(', ')'),
                    default => $this->value($elseValue)
                }
            );
        }

        return $caseExpression;
    }

    /**
     * {@inheritDoc}
     */
    public function quoteIdentifier(string $identifier): string
    {
        return $this->getDialect()->quoteIdentifier($identifier);
    }

    protected function getIdentifierQuoteChar(): string
    {
        return $this->getDialect()->getIdentifierQuoteChar();
    }

    /**
     * {@inheritDoc}
     */
    public function quoteQualifiedIdentifier(string $identifier): string
    {
        return $this->getDialect()->quoteIdentifier($identifier);
    }

    
    // protected function isBinding(string $value): bool
    // {
    //     return preg_match('/^:[a-zA-Z_][a-zA-Z0-9_]*$/', $value) === 1;
    // }

    // static public function isIdentifier(string $value): bool
    // {
    //     return preg_match('/^[a-zA-Z_][a-zA-Z0-9_\.]*$/', $value) === 1;
    // }

}
