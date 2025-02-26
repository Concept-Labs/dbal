<?php
namespace Concept\DBAL\DML\Builder;

use Concept\DBAL\DML\Builder\Contract\BindableInterface;
use Concept\DBAL\DML\Builder\Contract\ConditionableInterface;
use Concept\DBAL\DML\Builder\Contract\CTEableInterface;
use Concept\DBAL\DML\Builder\Contract\LimitableInterface;
use Concept\DBAL\DML\Builder\Contract\ShortcutableInterface;
use Concept\DBAL\DML\Expression\Contract\SqlExpressionAwareInterface;
use Concept\DBAL\DML\Expression\SqlExpressionInterface;
use Concept\DBC\Contract\ConnectionAwareInterface;
use Concept\DBC\Result\ResultInterface;
use Concept\Prototype\ResetableInterface;
use Stringable;

interface SqlBuilderInterface 
  extends 
    Stringable,
    ResetableInterface,
    SqlExpressionAwareInterface,
    ConnectionAwareInterface,
    LimitableInterface,
    ConditionableInterface,
    ShortcutableInterface,
    CTEableInterface,
    BindableInterface
{

    /**
     * Get the query as an expression
     * 
     * @return SqlExpressionInterface
     */
    public function asExpression(): SqlExpressionInterface;
   
   /**
     * Initialize the query builder
     * 
     * @return static
     */
    public function reset(?string $section = null): static;

    /**
     * Execute the query and return the result
     * 
     * @return ResultInterface
     */
    //public function query(): ResultInterface;
    /**
     * Execute the query and return the result
     * 
     * @return ResultInterface
     */
    public function execute(): ResultInterface;

    /**
     * Execute the query
     * 
     * @return int|bool
     */
    //public function exec(): int|bool;
}