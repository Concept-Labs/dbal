<?php
namespace Concept\DBAL\DML;

use Concept\DBAL\DML\Builder\Factory\InsertBuilderFactory;
use Concept\DBAL\DML\Builder\Factory\SelectBuilderFactory;
use Concept\DBAL\DML\Builder\InsertBuilderInterface;
use Concept\DBAL\DML\Builder\SelectBuilderInterface;
use Concept\DBAL\DML\Expression\SqlExpressionInterface;
use Concept\DBAL\Exception\InvalidArgumentException;
use Concept\Di\InjectableInterface;
use Concept\Di\InjectableTrait;

/*abstract*/ class DmlManager implements DmlManagerInterface, InjectableInterface
{
    use InjectableTrait;

    private ?SqlExpressionInterface $expression = null;
    private ?SelectBuilderFactory $selectBuilderFactory = null;
    private ?InsertBuilderFactory $insertBuilderFactory = null;

    public function __construct(
        SqlExpressionInterface $expression,
        SelectBuilderFactory $selectBuilderFactory,
        InsertBuilderFactory $insertBuilderFactory
    ) {
        $this->expression = $expression;
        $this->selectBuilderFactory = $selectBuilderFactory;
        $this->insertBuilderFactory = $insertBuilderFactory;

        $this->init();
    }

    /**
     * {@inheritDoc}
     */
    protected function init(): self
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function expression(...$expressions): SqlExpressionInterface
    {
        return clone($this->expression)->push(...$expressions);
    }

    /**
     * {@inheritDoc}
     */
    public function select(...$columns): SelectBuilderInterface
    {
        $this->validateColumns($columns);

        return $this->getSelectBuilder()->select(...$columns);
    }

    /**
     * {@inheritDoc}
     */
    public function insert(?string $table = null): InsertBuilderInterface
    {
        return $this->getInsertBuilder()->insert($table);
    }

    /**
     * Create a new select builder
     * 
     * @return SelectBuilderInterface
     */
    protected function getSelectBuilder(): SelectBuilderInterface
    {
        return $this->selectBuilderFactory->create();
    }

    /**
     * Create a new insert builder
     * 
     * @return InsertBuilderInterface
     */
    protected function getInsertBuilder(): InsertBuilderInterface
    {
        return $this->insertBuilderFactory->create();
    }

    /**
     * Validate the columns
     * 
     * @param array $columns The columns to validate
     * 
     * @throws \InvalidArgumentException If the columns are invalid
     * 
     * @return void
     * 
     
     */
    protected function validateColumns(array $columns): void
    {
        foreach ($columns as $column) {
            if (is_array($column)) {
                $this->validateColumns($column);
            } else if (!is_string($column) && !$column instanceof SqlExpressionInterface) {
                throw new InvalidArgumentException('Columns must be strings or SqlExpressionInterfaces');
            }
        }
    }

    
}