<?php
namespace Concept\DBAL\DML\Builder;

use Concept\DBAL\DML\Expression\SqlExpressionInterface;
use Concept\Di\InjectableInterface;

interface SqlBuilderInterface extends InjectableInterface //\Stringable
{
    /**
     * Get the query as a string
     * 
     * @return string
     */
   public function __toString(): string;

    /**
     * Get the query as an expression
     * 
     * @return SqlExpressionInterface
     */
    public function asExpression(): SqlExpressionInterface;
   
   /**
     * Initialize the query builder
     * 
     * @return self
     */
    public function reset(string $section = null): self;

    /**
     * Add a keyword to the query
     * 
     * @param string $keyword The keyword to add
     * 
     * @return SqlExpressionInterface
     */
    public function keyword(string $keyword): SqlExpressionInterface;

    /**
     * Add an identifier to the query
     * 
     * @param string $identifier The identifier to add
     * 
     * @return SqlExpressionInterface
     */
    public function identifier(string $identifier): SqlExpressionInterface;

    /**
     * Add a WHERE to the query
     * Pass the conditions as arguments
     * Agruments can be strings, Stringables or QueryExpressionInterfaces
     * ('condition', 'condition', ...)
     * To set column or expression aliases, 
     * pass an array with the column/expression as key and the alias as value:
     * ('condition', ['alias' => 'condition'], ...)
     * ('condition', 'condition', ['alias' => <QueryExpressionInterface>, ...]
     * 
     * 
     * @param string|Stringable|QueryExpressionInterface ...$conditions The conditions to add
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException If the conditions are empty
     */
    public function where(...$conditions): self;

    /**
     * Add a OR WHERE to the query
     * Pass the conditions as arguments
     * Agruments can be strings, Stringables or QueryExpressionInterfaces
     * ('condition', 'condition', ...)
     * To set column or expression aliases, 
     * pass an array with the column/expression as key and the alias as value:
     * ('condition', ['alias' => 'condition'], ...)
     * ('condition', 'condition', ['alias' => <QueryExpressionInterface>, ...]
     * 
     * 
     * @param string|Stringable|QueryExpressionInterface ...$conditions The conditions to add
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException If the conditions are empty
     */
    public function orWhere(...$conditions): self;

    

   /**
     * Add a LIMIT to the query
     * 
     * @param int $limit  The limit value
     * @param int $offset The offset value
     * 
     * @return self
     */

     public function limit(int $limit, int $offset = null): self;



}