<?php
namespace Concept\DBAL\DDL;

use Concept\DBAL\DML\Expression\Contract\SqlExpressionAwareTrait;
use Concept\DBAL\DDL\Builder\Factory\CreateTableBuilderFactoryInterface;
use Concept\DBAL\DDL\Builder\Factory\AlterTableBuilderFactoryInterface;
use Concept\DBAL\DDL\Builder\Factory\DropTableBuilderFactoryInterface;
use Concept\DBAL\DDL\Builder\Factory\TruncateTableBuilderFactoryInterface;
use Concept\DBAL\DDL\Builder\CreateTableBuilderInterface;
use Concept\DBAL\DDL\Builder\AlterTableBuilderInterface;
use Concept\DBAL\DDL\Builder\DropTableBuilderInterface;
use Concept\DBAL\DDL\Builder\TruncateTableBuilderInterface;
use Concept\DBAL\DML\Expression\SqlExpressionInterface;
use Concept\DBAL\Exception\RuntimeException;
use Concept\DBC\ConnectionInterface;
use Concept\Singularity\Contract\Lifecycle\SharedInterface;

class DdlManager 
    implements 
    DdlManagerInterface, SharedInterface
{

    use SqlExpressionAwareTrait;

    private ?CreateTableBuilderInterface $createTableBuilderPrototype = null;

    private ?AlterTableBuilderInterface $alterTableBuilderPrototype = null;

    private ?DropTableBuilderInterface $dropTableBuilderPrototype = null;

    private ?TruncateTableBuilderInterface $truncateTableBuilderPrototype = null;

    public function __construct(
        private ConnectionInterface $connection,
        private SqlExpressionInterface $sqlExpressionPrototype,
        private CreateTableBuilderFactoryInterface $createTableBuilderFactory,
        private AlterTableBuilderFactoryInterface $alterTableBuilderFactory,
        private DropTableBuilderFactoryInterface $dropTableBuilderFactory,
        private TruncateTableBuilderFactoryInterface $truncateTableBuilderFactory
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function createTable(string $table): CreateTableBuilderInterface
    {
        return $this->getCreateTableBuilder()->createTable($table);
    }

    /**
     * {@inheritDoc}
     */
    public function alterTable(string $table): AlterTableBuilderInterface
    {
        return $this->getAlterTableBuilder()->alterTable($table);
    }

    /**
     * {@inheritDoc}
     */
    public function dropTable(string $table): DropTableBuilderInterface
    {
        return $this->getDropTableBuilder()->dropTable($table);
    }

    /**
     * {@inheritDoc}
     */
    public function truncateTable(string $table): TruncateTableBuilderInterface
    {
        return $this->getTruncateTableBuilder()->truncateTable($table);
    }

    /**
     * Create a new create table builder
     * 
     * @return CreateTableBuilderInterface
     */
    protected function getCreateTableBuilder(): CreateTableBuilderInterface
    {
        if (null === $this->createTableBuilderPrototype) {
            if (null === $this->createTableBuilderFactory) {
                throw new RuntimeException('No create table builder factory has been set');
            }

            $this->createTableBuilderPrototype = $this->createTableBuilderFactory->create();
        }

        return clone $this->createTableBuilderPrototype;
    }

    /**
     * Create a new alter table builder
     * 
     * @return AlterTableBuilderInterface
     */
    protected function getAlterTableBuilder(): AlterTableBuilderInterface
    {
        if (null === $this->alterTableBuilderPrototype) {
            if (null === $this->alterTableBuilderFactory) {
                throw new RuntimeException('No alter table builder factory has been set');
            }

            $this->alterTableBuilderPrototype = $this->alterTableBuilderFactory->create();
        }

        return clone $this->alterTableBuilderPrototype;
    }

    /**
     * Create a new drop table builder
     * 
     * @return DropTableBuilderInterface
     */
    protected function getDropTableBuilder(): DropTableBuilderInterface
    {
        if (null === $this->dropTableBuilderPrototype) {
            if (null === $this->dropTableBuilderFactory) {
                throw new RuntimeException('No drop table builder factory has been set');
            }

            $this->dropTableBuilderPrototype = $this->dropTableBuilderFactory->create();
        }

        return clone $this->dropTableBuilderPrototype;
    }

    /**
     * Create a new truncate table builder
     * 
     * @return TruncateTableBuilderInterface
     */
    protected function getTruncateTableBuilder(): TruncateTableBuilderInterface
    {
        if (null === $this->truncateTableBuilderPrototype) {
            if (null === $this->truncateTableBuilderFactory) {
                throw new RuntimeException('No truncate table builder factory has been set');
            }

            $this->truncateTableBuilderPrototype = $this->truncateTableBuilderFactory->create();
        }

        return clone $this->truncateTableBuilderPrototype;
    }
    
}
