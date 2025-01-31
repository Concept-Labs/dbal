<?php
namespace Concept\DBAL\DML\Builder;


use Concept\DBC\Result\ResultInterface;
use Concept\DBAL\DML\Builder\Contract\Traits\BindableTrait;
use Concept\DBAL\DML\Builder\Contract\Traits\ConditionTrait;
use Concept\DBAL\DML\Expression\SqlExpressionInterface;
use Concept\DBAL\DML\Builder\Contract\Traits\CTETrait;
use Concept\DBAL\DML\Builder\Contract\Traits\LimitTrait;
use Concept\DBAL\DML\Builder\Contract\Traits\ShortcutTrait;
use Concept\DBAL\DML\Expression\Contract\SqlExpressionAwareTrait;
use Concept\DBAL\Exception\InvalidArgumentException;
use Concept\DBAL\Exception\RuntimeException;
use Concept\DBC\Contract\ConnectionAwareTrait;
use Concept\DI\Factory\Attribute\Dependent;


#[Dependent]
abstract class SqlBuilder 
    implements 
        SqlBuilderInterface
{
    use ConnectionAwareTrait;
    use SqlExpressionAwareTrait;
    use ShortcutTrait;
    use CTETrait;
    use ConditionTrait;
    use LimitTrait;
    use BindableTrait;


    protected array $sections = [];
    protected array $bindings = [];

    public function __clone(){}

    public function prototype(): static
    {
        return clone $this;
    }
    
    /**
     * {@inheritDoc}
     */
    public function asExpression(): SqlExpressionInterface
    {
        return $this->getPipeline();
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
    public function reset(string $section = null): static
    {
        if ($section === null) {
            $this->sections = [];
        } else if ($section && isset($this->sections[$section])) {
            unset($this->sections[$section]);
        }

        return $this;
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
        $list = $this->expression()->join(', ')->type(SqlExpressionInterface::TYPE_LIST);
        
        foreach ($this->normalizeArguments(...$expressions) as $alias => $expression) {

            $list->push(
                match(true) {
                    is_numeric($alias) => match(true) {
                        $expression instanceof SqlExpressionInterface => $expression->wrap('(', ')'),
                        '*' == $expression => $expression,
                        default => $this->expression()->identifier($expression)
                    },
                    // $expression instanceof SqlExpressionInterface
                    //     ? $expression->wrap('(', ')')
                    //     : ('*' == $expression ? $expression : $this->expression()->identifier($expression)),
                    default => $this->expression()->alias($alias, $expression)
                }
            );

            // if (is_numeric($alias)) {
            //     $list->push(
            //         $expression instanceof SqlExpressionInterface
            //             ? $expression->wrap('(', ')')
            //             : ('*' == $expression ? $expression : $this->expression()->identifier($expression))
            //     );
            // } else {
            //     $list->push(
            //         $this->expression()->alias($alias, $expression)
            //     );
            // }
        }

        return $list;
    }

    /**
     * Get a section
     * If the section does not exist, create it
     * 
     * @param string $section The section to get
     * 
     * @return SqlExpressionInterface
     */
    protected function getSection(string $section, bool $create = true): ?SqlExpressionInterface
    {
        return $this->sections[$section] 
            ?? (
                $create ? $this->sections[$section] = $this->expression()->type('section') : null
            );
    }

    /**
     * Get the pipeline
     * 
     * @return SqlExpressionInterface
     */
    abstract protected function getPipeline(): SqlExpressionInterface;

    /**
     * Pipe a section.
     * 
     * @param string $section The section to pipe
     * 
     * @return SqlExpressionInterface|null
     */
    protected function pipeSection(string $section, bool $useKeyword = true): ?SqlExpressionInterface
    {
        return $this->getSection($section, false)
            ?   $this->expression(
                    $useKeyword ? $this->expression()->keyword($section) : null, 
                    $this->getSection($section, false)
                )->type('pipe')
            :   null;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(): ResultInterface
    {
        if (!$this->hasConnection()) {
            throw new RuntimeException('No connection set');
        }

        if (!$this->getConnection()->isConnected()) {
            $this->getConnection()->connect();
        }

        $sql = (string)$this->asExpression();

        if (empty($sql)) {
            throw new RuntimeException('No query to execute');
        }

        $params = $this->getBindings();

        return $this->getConnection()
            ->getDriver()
                ->execute($sql, $params);
    }

}