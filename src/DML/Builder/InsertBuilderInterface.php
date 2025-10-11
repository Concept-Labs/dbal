<?php
namespace Concept\DBAL\DML\Builder;

use Concept\DBAL\Expression\SqlExpressionInterface;
use Stringable;

interface InsertBuilderInterface extends SqlBuilderInterface
{
     /**
     * Initialize the query as an INSERT
     * 
     * @return static
     */
    public function insert(): static;

    /**
     * Use the IGNORE keyword
     * 
     * @return static
     */
    public function ignore(): static;

    /**
     * Add a INTO to the query
     * 
     * @param string $table The table to insert into
     * @param string|null $alias The table alias
     * 
     * @return static
     */
    public function into(string $table): static;

    /**
     * Add a COLUMNS to the query
     * 
     * @param string|Stringable|string[]|Stringable[] ...$columns The columns to add
     * 
     * @return static
     * 
     * @throws InvalidArgumentException If the columns are empty
     */
    //public function columns(string|array|Stringable ...$columns): static;

    /**
     * Add a VALUES to the query
     * 
     * @param array $values The values to add
     * 
     * @return static
     * 
     * @throws InvalidArgumentException If the values are empty
     */
    public function values(array $values): static;

    /**
     * Add a SELECT to the query
     * 
     * @param SqlExpressionInterface $select The select query
     * 
     * @return static
     */
    public function fromSelect(SqlExpressionInterface $select): static;

    /**
     * Add an ON DUPLICATE KEY UPDATE to the query
     * 
     * @param array|null $columns The columns to update. If null, ignore the duplicate key
     * $columns: must be an array with the column as key and the value as scalar value
     *           or as SqlExpressionInterface
     *           (['column' => 'value', 'column' => <SqlExpressionInterface>, ...])
     * 
     * @return static
     */
    public function onDuplicateKey(array $columns): static;

    /**
     * Add a RETURNING to the query
     * 
     * @param string|Stringable ...$columns The columns to return
     * 
     * @return static
     */
    public function returning(string|Stringable ...$columns): static;
}