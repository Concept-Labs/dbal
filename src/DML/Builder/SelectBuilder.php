<?php
namespace Concept\DBAL\DML\Builder;

use Concept\DBAL\DML\Decorator\Decorator;
use Concept\DBAL\DML\Expression\KeywordEnum;

class SelectBuilder extends SqlBuilder implements SelectBuilderInterface
{
     /**
     * {@inheritDoc}
     */
    public function distinct(): self
    {
        $this->getSection(KeywordEnum::SELECT)->decorate(
            fn($value) => KeywordEnum::DISTINCT . ' ' . $value
        );

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function select(...$columns): self
    {
        $this->getSection(KeywordEnum::SELECT)
            ->push(
                $this->aggregateAliasableList(...$columns)
            );

        return $this;
    }

    /**
     * Add a FROM to the query
     * 
     * @param string|Stringable|QueryExpressionInterface ...$tables The tables to add
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException If the tables are empty
     */
    public function from(...$tables): self
    {
        $this->getSection(KeywordEnum::FROM)
            ->push(
                $this->aggregateAliasableList(...$tables)
            );

        return $this;
    }

    /**
     * Add a LEFT JOIN to the query
     * 
     * @param string|Stringable|QueryExpressionInterface $table         The table to join
     * @param string                                    $alias          The alias for the table
     * @param string|Stringable|QueryExpressionInterface ...$expressions The expressions to join on
     * 
     * @return self
     */
    public function join($table, string $alias, ...$expressions): self
    {
        return $this->_join(KeywordEnum::INNER_JOIN, $table, $alias, ...$expressions);
    }

    /**
     * Add a LEFT JOIN to the query
     * 
     * @param string|Stringable|QueryExpressionInterface $table         The table to join
     * @param string                                    $alias          The alias for the table
     * @param string|Stringable|QueryExpressionInterface ...$expressions The expressions to join on
     * 
     * @return self
     */
    public function leftJoin($table, string $alias, ...$expressions): self
    {
        return $this->_join(KeywordEnum::LEFT_JOIN, $table, $alias, ...$expressions);
    }

    /**
     * Add a RIGHT JOIN to the query
     * 
     * @param string|Stringable|QueryExpressionInterface $table         The table to join
     * @param string                                    $alias          The alias for the table
     * @param string|Stringable|QueryExpressionInterface ...$expressions The expressions to join on
     * 
     * @return self
     */
    public function rightJoin($table, string $alias, ...$expressions): self
    {
        return $this->_join(KeywordEnum::RIGHT_JOIN, $table, $alias, ...$expressions);
    }

    /**
     * Add a JOIN to the query
     * 
     * @param string                                        $section        The section of join to add (INNER JOIN, LEFT JOIN, RIGHT JOIN)
     * @param string|Stringable|QueryExpressionInterface    $table          The table to join
     * @param string                                        $alias          The alias for the table
     * @param string|Stringable|QueryExpressionInterface    ...$expressions The expressions to join on
     * 
     * @return self
     */
    protected function _join(string $type, $table, string $alias, ...$expressions): self
    {
        $this->getSection(KeywordEnum::JOIN)
            ->push(
                $type,
                $this->aggregateAliasableList([$alias => $table]),
                KeywordEnum::ON,
                $this->aggregateConditions(...$expressions)
            );
        
        return $this;
    }

    /**
     * Add a HAVING to the query
     * 
     * @param string|Stringable|QueryExpressionInterface ...$expressions The expressions to add
     * 
     * @return self
     */
    public function having(...$expressions): self
    {
        return $this->addConditionToSection(KeywordEnum::HAVING, KeywordEnum::AND, ...$expressions);
    }

    /**
     * Add an OR HAVING to the query
     * 
     * @param string|Stringable|QueryExpressionInterface ...$expressions The expressions to add
     * 
     * @return self
     */
    public function orHaving(...$expressions): self
    {
        return $this->addConditionToSection(KeywordEnum::HAVING, KeywordEnum::OR, ...$expressions);
    }

    /**
     * Add a group by to the query
     * 
     * @param string|Stringable|QueryExpressionInterface ...$columns The columns to group by
     * 
     * @return self
     */
    public function groupBy(...$columns): self
    {
        $this->getSection(KeywordEnum::GROUP_BY)
            ->push(
                ...$this->aggregateAliasableList(...$columns)
            );

        return $this;
    }

    /**
     * @see groupBy()
     */
    public function group(...$columns): self
    {
        return $this->groupBy(...$columns);
    }

    /**
     * Add an order by to the query
     * 
     * @param string|Stringable|QueryExpressionInterface ...$columns The columns to order by
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException If the columns are empty
     * @throws \InvalidArgumentException If the column is not a string or QueryExpressionInterface
     */
    public function orderBy(...$columns): self
    {
        $this->getSection(KeywordEnum::ORDER_BY)
            ->push(
                $this->aggregateAliasableList(...$columns)
            );

        return $this;
    }

    /**
     * @see orderBy()
     */
    public function order(...$columns): self
    {
        return $this->orderBy(...$columns);
    }

    
    protected function getPipeline(): iterable
    {
            return  [
                KeywordEnum::SELECT => $this->expression()
                    ->decorate(Decorator::wrapper(KeywordEnum::SELECT, ''))
                    ->decorateJoin(Decorator::joiner(', ')),
                    
                KeywordEnum::FROM => $this->expression()
                    ->decorate(Decorator::wrapper(KeywordEnum::FROM, ''))
                    ->decorateJoin(Decorator::joiner(', ')),
                    
                KeywordEnum::JOIN => $this->expression(),
                
                KeywordEnum::WHERE => $this->expression()
                    ->decorate(Decorator::wrapper(KeywordEnum::WHERE, '')),

                KeywordEnum::GROUP_BY => $this->expression()
                    ->decorate(Decorator::wrapper(KeywordEnum::GROUP_BY, ''))
                    ->decorateJoin(Decorator::joiner(', ')),

                KeywordEnum::HAVING => $this->expression()
                    ->decorate(Decorator::wrapper(KeywordEnum::HAVING, '')),

                KeywordEnum::ORDER_BY => $this->expression()
                    ->decorate(Decorator::wrapper(KeywordEnum::ORDER_BY, ''))
                    ->decorateJoin(Decorator::joiner(', ')),

                KeywordEnum::LIMIT => $this->expression()
                    ->decorate(Decorator::wrapper(KeywordEnum::LIMIT, '')),
            ];

        
    }
}