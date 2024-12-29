<?php
namespace Concept\DBAL\DML\Builder;

use Concept\DBAL\DML\Decorator\Decorator;
use Concept\DBAL\DML\Expression\KeywordEnum;
use Concept\DBAL\DML\Expression\SqlExpressionInterface;
use Concept\Di\InjectableTrait;
use Concept\Prototype\ResetableInterface;

abstract class SqlBuilder /*extends SqlExpression*/ implements SqlBuilderInterface, ResetableInterface
{
    use InjectableTrait;

    /**
     * The query expression prototype
     * 
     * @var SqlExpressionInterface
     */
    private ?SqlExpressionInterface $queryExpressionPrototype = null;

    protected array $sections = [];

    public function __construct(SqlExpressionInterface $expression) 
    {
        $this->queryExpressionPrototype = $expression;

        $this->init();
    }

    public function __invoke(...$expressions): SqlExpressionInterface
    {
        return $this->asExpression()->push(...$expressions);
    }

    /**
     * {@inheritDoc}
     */
    public function reset(string $section = null): self
    {
        if ($section === null) {
            $this->sections = [];
        } else if ($section) {
            $this->sections[$section] = null;
        }
        return $this->init();
    }

    /**
     * Initialize the query builder
     */
    protected function init(): self
    {
        return $this;
    }

    /**
     * Add a WHERE to the query
     * 
     * @param string|Stringable|QueryExpressionInterface ...$expressions The expressions to add
     * 
     * @return self
     */
    public function where(...$expressions): self
    {
        return $this->addConditionToSection(KeywordEnum::WHERE, KeywordEnum::AND, ...$expressions);
    }

    /**
     * Add an OR WHERE to the query
     * 
     * @param string|Stringable|QueryExpressionInterface ...$expressions The expressions to add
     * 
     * @return self
     */
    public function orWhere(...$expressions): self
    {
        return $this->addConditionToSection(KeywordEnum::WHERE, KeywordEnum::OR, ...$expressions);
    }
    
    /**
     * Get a new expression prototype
     * 
     * @param SqlExpressionInterface ...$expressions The expressions to add
     * 
     * @return SqlExpressionInterface
     */
    public function expression(...$expressions): SqlExpressionInterface
    {
        return $this->getExpressionPrototype(...$expressions);
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return (string) $this->asExpression();
    }

    /**
     * {@inheritDoc}
     */
    public function asExpression(): SqlExpressionInterface
    {
        $queryExpression = $this->expression()->join(' ');

        /**@var SqlExpressionInterface $expression */
        foreach ($this->getPipeline() as $section => $expression) {
            if (empty($this->sections[$section]) || $this->sections[$section]->count() === 0) {
                continue;
            }
            
            $expression = $expression ?? $this->expression();
            $expression->push($this->getSection($section));

            $queryExpression->push($expression);
        }

        return $queryExpression;
    }
    
    /**
     * Add a limit to the query
     * 
     * @param int $limit  The limit value
     * @param int $offset The offset value
     * 
     * @return self
     */
    public function limit(int $limit, int $offset = null): self
    {
        $this->getSection(KeywordEnum::LIMIT)
            ->reset()
            ->push(
                $limit,
                $offset ? $this->keyword(KeywordEnum::OFFSET) : null,
                $offset ?? null
            );

        return $this;
    }

    /**
     * Get a new expression prototype
     * 
     * @param SqlExpressionInterface ...$expressions The expressions to add
     * 
     * @return SqlExpressionInterface
     */
    protected function getExpressionPrototype(...$expressions): SqlExpressionInterface
    {
        return (clone $this->queryExpressionPrototype)->push(...$expressions);
    }

    /**
     * Normalize arguments to an array with the following rules:
     * 1. If the argument is an array, merge it with the result
     * 2. If the argument is not an array, add it to the result
     * Allows to pass multiple arguments arguments as string or array
     * - select('column1', 'column2', ['alias' => 'column4'], ['alias2' => <QueryExpression>])
     * - from('table1', ['alias' => 'table2'], ['alias2' => <QueryExpression>])
     * 
     * @param mixed ...$arguments The expressions to normalize
     * 
     * @return array
     */
    protected function normalizeArguments(...$arguments) {
        return array_merge([], ...array_map(function($argument) {
            return is_array($argument) ? $argument : [$argument];
        }, $arguments));
    }

    /**
     * Create a list of expressions that can be aliased
     * Normalizes the arguments to an array
     * Acceted formats:
     * - aggregateAliasableList('column1', 'column2', ['alias' => 'column4'], ['alias2' => <QueryExpression>])
     * used for components like  SELECT, FROM, GROUP BY, ORDER BY, etc
     * 
     * @param mixed  ...$expressions The expressions to add
     * 
     * @return SqlExpressionInterface The expression object containing the aggregated expressions
     * 
     * @throws \InvalidArgumentException If the expressions are empty
     */
    protected function aggregateAliasableList(...$expressions): SqlExpressionInterface
    {
        //@todo: decorate directly before return
        $list = $this->expression()->decorateJoin(Decorator::joiner(', '));
        
        foreach ($this->normalizeArguments(...$expressions) as $alias => $expression) {
            $list->push(
                $this->expression(
                    $expression instanceof SqlExpressionInterface
                        ? $expression->wrap('(', ')')
                        : $this->identifier($expression),
                    is_numeric($alias) 
                        ? null 
                        : $this->expression(
                            $this->keyword(KeywordEnum::AS),
                            $this->identifier($alias)
                        )
                )
            );
        }

        return $list;
    }

    /**
     * Add a condition to the section
     * 
     * @param string $section       The section to use
     * @param string $left          The left expression to use
     * @param mixed ...$expressions The expressions to add
     * 
     * @return self
     */
    protected function addConditionToSection(string $section, string $left, ...$expressions): self
    {
        $this->getSection($section)
            ->push(
                $this->getSection($section)->count() > 0 ? $this->expression()->keyword($left) : null,
                $this->aggregateConditions(...$expressions)
            );

        return $this;
    }

    /**
     * Aggregate conditions
     * 
     * @param mixed ...$expressions The expressions
     * 
     * @return self
     */
    protected function aggregateConditions(...$expressions): SqlExpressionInterface
    {
        return $this->expression(...$expressions)
            ->join($this->keyword(KeywordEnum::AND))
            ->wrapItem(' ')
            ->wrap('(', ')')
            ;
    }

    /**
     * Get a section
     * If the section does not exist, create it
     * 
     * @param string $section The section to get
     * 
     * @return SqlExpressionInterface
     */
    protected function getSection(string $section): SqlExpressionInterface
    {
        return $this->sections[$section] ?? $this->sections[$section] = $this->expression();
    }

    
    

    public function keyword(string $keyword): SqlExpressionInterface
    {
        return $this->expression()->keyword($keyword);
    }

    public function identifier(string $identifier): SqlExpressionInterface
    {
        return $this->expression()->identifier($identifier);
    }

    /**
     * Get the pipeline
     * 
     * @return iterable
     */
    protected function getPipeline(): iterable
    {
        return [];
    }

}
