<?php
namespace Concept\DBAL\DML\Builder\Contract\Traits;

use Concept\DBAL\Expression\KeywordEnum;
use Concept\DBAL\Expression\SqlExpressionInterface;

trait JoinTrait
{
    /**
     * Add a JOIN to the query
     * 
     * @param string                                        $section        The section of join to add (INNER JOIN, LEFT JOIN, RIGHT JOIN)
     * @param string|Stringable|QueryExpressionInterface    $table          The table to join
     * @param string                                        $alias          The alias for the table
     * @param string|Stringable|QueryExpressionInterface    ...$expressions The expressions to join on
     * 
     * @return static
     */
    protected function _join(string $type, $table, string $alias, ...$expressions): static
    {
        $this->getSection(KeywordEnum::JOIN)
            ->push(
                $this->expression(
                    $this->expression()->keyword($type),
                    $this->aggregateAliasableList([$alias => $table]),
                    $this->expression()->keyword(KeywordEnum::ON),
                    $this->aggregateConditions(...$expressions)
                )->type(SqlExpressionInterface::TYPE_GROUP)
            );
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function join($table, string $alias, ...$expressions): static
    {
        return $this->_join(KeywordEnum::INNER_JOIN, $table, $alias, ...$expressions);
    }

    /**
     * {@inheritDoc}
     */
    public function leftJoin($table, string $alias, ...$expressions): static
    {
        return $this->_join(KeywordEnum::LEFT_JOIN, $table, $alias, ...$expressions);
    }

    /**
     * {@inheritDoc}
     */
    public function rightJoin($table, string $alias, ...$expressions): static
    {
        return $this->_join(KeywordEnum::RIGHT_JOIN, $table, $alias, ...$expressions);
    }

    //---???
    public function joinUsing($table, string $alias, ...$columns): static
    {
        if (empty($columns)) {
            throw new \InvalidArgumentException("JOIN ... USING requires at least one column.");
        }

        $this->getSection(KeywordEnum::JOIN)
            ->push(
                KeywordEnum::INNER_JOIN,
                $this->aggregateAliasableList([$alias => $table]),
                KeywordEnum::USING,
                $this->expression()->wrap('(', ')')->push(
                    $this->aggregateAliasableList(...$columns)
                )
            );
        return $this;
    }
}