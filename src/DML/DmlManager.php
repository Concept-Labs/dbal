<?php
namespace Concept\DBAL\DML;

use Concept\DBAL\Expression\Contract\SqlExpressionAwareTrait;
use Concept\DBAL\DML\Builder\Factory\DeleteBuilderFactoryInterface;
use Concept\DBAL\DML\Builder\Factory\InsertBuilderFactoryInterface;
use Concept\DBAL\DML\Builder\Factory\RawBuilderFactoryInterface;
use Concept\DBAL\DML\Builder\Factory\SelectBuilderFactoryInterface;
use Concept\DBAL\DML\Builder\Factory\UpdateBuilderFactoryInterface;
use Concept\DBAL\DML\Builder\RawBuilderInterface;
use Concept\DBAL\DML\Builder\InsertBuilderInterface;
use Concept\DBAL\DML\Builder\SelectBuilderInterface;
use Concept\DBAL\DML\Builder\SqlBuilderInterface;
use Concept\DBAL\DML\Builder\UpdateBuilderInterface;
use Concept\DBAL\DML\Builder\DeleteBuilderInterface;
use Concept\DBAL\Expression\SqlExpressionInterface;
use Concept\DBAL\Exception\RuntimeException;
use Concept\DBC\ConnectionInterface;
use Concept\Singularity\Contract\Lifecycle\SharedInterface;

class DmlManager 
    implements 
    DmlManagerInterface, SharedInterface
{

    use SqlExpressionAwareTrait;

    private ?RawBuilderInterface $rawBuilderPrototype = null;

    private ?SelectBuilderInterface $selectBuilderPrototype = null;

    private ?InsertBuilderInterface $insertBuilderPrototype = null;

    private ?UpdateBuilderInterface $updateBuilderPrototype = null;

    private ?DeleteBuilderInterface $deleteBuilderPrototype = null;

    public function __construct(
        private ConnectionInterface $connection,
        private SqlExpressionInterface $sqlExpressionPrototype,
        private RawBuilderFactoryInterface $rawBuilderFactory,
        private SelectBuilderFactoryInterface $selectBuilderFactory,
        private InsertBuilderFactoryInterface $insertBuilderFactory,
        private UpdateBuilderFactoryInterface $updateBuilderFactory,
        private DeleteBuilderFactoryInterface $deleteBuilderFactory
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function select(...$columns): SelectBuilderInterface
    {
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
     * {@inheritDoc}
     */
    public function update(string|array $table): UpdateBuilderInterface
    {
        return $this->getUpdateBuilder()->update($table);
    }


    /**
     * {@inheritDoc}
     */
    public function delete(?string $table = null): DeleteBuilderInterface
    {
        return $this->getDeleteBuilder()->delete($table);
    }
    
    /**
     * {@inheritDoc}
     */
    public function raw(string|SqlBuilderInterface ...$sql): RawBuilderInterface
    {
        return $this->getRawBuilder()->raw(...$sql);
    }

    /**
     * Create a new raw builder
     * 
     * @return RawBuilderInterface
     */
    protected function getRawBuilder(): RawBuilderInterface
    {
        if (null === $this->rawBuilderPrototype) {
            if (null === $this->rawBuilderFactory) {
                throw new RuntimeException('No raw builder factory has been set');
            }

            $this->rawBuilderPrototype = $this->rawBuilderFactory->create();
        }

        return clone $this->rawBuilderPrototype;
    }

    /**
     * Create a new select builder
     * 
     * @return SelectBuilderInterface
     */
    protected function getSelectBuilder(): SelectBuilderInterface
    {
        if (null === $this->selectBuilderPrototype) {
            if (null === $this->selectBuilderFactory) {
                throw new RuntimeException('No select builder factory has been set');
            }

            $this->selectBuilderPrototype = $this->selectBuilderFactory->create();
        }

        return clone $this->selectBuilderPrototype;
    }

    /**
     * Create a new insert builder
     * 
     * @return InsertBuilderInterface
     */
    protected function getInsertBuilder(): InsertBuilderInterface
    {
        if (null === $this->insertBuilderPrototype) {
            if (null === $this->insertBuilderFactory) {
                throw new RuntimeException('No insert builder factory has been set');
            }

            $this->insertBuilderPrototype = $this->insertBuilderFactory->create();
        }

        return clone $this->insertBuilderPrototype;
    }

    /**
     * Create a new update builder
     * 
     * @return UpdateBuilderInterface
     */
    protected function getUpdateBuilder(): UpdateBuilderInterface
    {
        if (null === $this->updateBuilderPrototype) {
            if (null === $this->updateBuilderFactory) {
                throw new RuntimeException('No update builder factory has been set');
            }

            $this->updateBuilderPrototype = $this->updateBuilderFactory->create();
        }

        return clone $this->updateBuilderPrototype;
    }

    /**
     * Create a new delete builder
     * 
`     * @return DeleteBuilderInterface
     */
    protected function getDeleteBuilder(): DeleteBuilderInterface
    {
        if (null === $this->deleteBuilderPrototype) {
            if (null === $this->deleteBuilderFactory) {
                throw new RuntimeException('No delete builder factory has been set');
            }

            $this->deleteBuilderPrototype = $this->deleteBuilderFactory->create();
        }

        return clone $this->deleteBuilderPrototype;
    }
    
}