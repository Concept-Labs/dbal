<?php
namespace Concept\DBAL\DML\Builder;

use Concept\DBAL\DML\Expression\SqlExpressionInterface;

interface InsertBuilderInterface extends SqlBuilderInterface
{
     /**
     * Initialize the query as an INSERT
     * 
     * @return self
     */
    public function insert(): self;

    /**
     * Use the IGNORE keyword
     * 
     * @return self
     */
    public function ignore(): self;

    /**
     * Use the DELAYED keyword
     * 
     * @return self
     */
    public function delayed(): self;

    /**
     * Add a INTO to the query
     * 
     * @param string $table The table to insert into
     * @param string|null $alias The table alias
     * 
     * @return self
     */
    public function into(string $table): self;

    /**
     * Add a COLUMNS to the query
     * 
     * @param string ...$columns The columns to add
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException If the columns are empty
     */
    public function columns(string ...$columns): self;

    /**
     * Add a VALUES to the query
     * Pass the values as arguments
     * Agruments must be arrays with the values to add
     * ([value, value, ...], [value, value, ...], ...)
     * 
     * @param array|SqlExpressionInterface ...$values The values to add
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException If the values are empty
     */
    public function values(...$values): self;

    /**
     * Add a SELECT to the query
     * 
     * @param SqlExpressionInterface $select The select query
     * 
     * @return self
     */
    public function fromSelect(SqlExpressionInterface $select): self;

    /**
     * Add an ON DUPLICATE KEY UPDATE to the query
     * 
     * @param array|null $columns The columns to update. If null, ignore the duplicate key
     * $columns: must be an array with the column as key and the value as scalar value
     *           or as SqlExpressionInterface
     *           (['column' => 'value', 'column' => <SqlExpressionInterface>, ...])
     * 
     * @return self
     */
    public function onDuplicateKey(?array $columns = null): self;
}