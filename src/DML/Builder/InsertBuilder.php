<?php
namespace Concept\DBAL\DML\Builder;

use Concept\DBAL\DML\Expression\SqlExpressionInterface;
use Concept\DBAL\DML\Expression\KeywordEnum;
use Concept\DBAL\DML\Decorator\Decorator;

class InsertBuilder extends SqlBuilder implements InsertBuilderInterface
{

    /**
     * Initialize the query as an INSERT
     * 
     * @return self
     */
    public function insert(?string $table = null): self
    {
        $this->getSection(KeywordEnum::INSERT)->reset()->push(KeywordEnum::INSERT);
        
        if ($table !== null) {
            $this->into($table);
        }

        return $this;
    }

    /**
     * Use the IGNORE keyword
     * 
     * @return self
     */
    public function ignore(): self
    {
        $this->getSection(KeywordEnum::IGNORE)->reset()->push(KeywordEnum::IGNORE);

        return $this;
    }

    /**
     * Use the DELAYED keyword
     * 
     * @return self
     */
    public function delayed(): self
    {
        $this->getSection(KeywordEnum::DELAYED)->reset()->push(KeywordEnum::DELAYED);

        return $this;
    }

    /**
     * Add a INTO to the query
     * 
     * @param string $table The table to insert into
     * 
     * @return self
     */
    public function into(string $table): self
    {
        $this->getSection(KeywordEnum::INTO)
            ->reset()
            ->push(
                $this->expression()->identifier($table)
            );

        return $this;
    }

    /**
     * Add a COLUMNS to the query
     * 
     * @param string ...$columns The columns to add
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException If the columns are empty
     */
    public function columns(string ...$columns): self
    {
        $this->getSection(KeywordEnum::COLUMNS)->push($this->aggregateAliasableList(...$columns));

        return $this;
    }

    /**
     * Add a VALUES to the query
     * Pass the values as arguments
     * Agruments must be arrays with the values to add
     * ([value, value, ...], [value, value, ...], ...)
     * 
     * @param array ...$values The values to add
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException If the values are empty
     */
    public function values(...$values): self
    {

        $this->getSection(KeywordEnum::SELECT)->reset();

        $this->getSection(KeywordEnum::VALUES)
            ->push(
                ...array_map(
                    function ($value) {
                        return $this->expression(KeywordEnum::VALUES, ...$value);
                    },
                    $values
                )
            );

        return $this;
    }

    /**
     * Add a SELECT to the query
     * 
     * @param SqlExpressionInterface $select The select query
     * 
     * @return self
     */
    public function fromSelect(SqlExpressionInterface $select): self
    {
        $this->getSection(KeywordEnum::VALUES)->reset();


        $this->getSection(KeywordEnum::SELECT)
            ->reset()
            ->push($this->expression(KeywordEnum::SELECT, $select));

        return $this;
    }

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
    public function onDuplicateKey(?array $columns = null): self
    {
        if ($columns === null) {
            $this->getSection(KeywordEnum::ON_DUPLICATE_KEY)->reset();
            return $this;
        }

        $this->getSection(KeywordEnum::ON_DUPLICATE_KEY)
            ->push(
                $this->expression(
                    KeywordEnum::ON_DUPLICATE_KEY,
                    ...array_map(
                        fn($column, $value) => $this->expression(
                            sprintf('%s = %s', 
                                $this->expression($column)->decorate(Decorator::identifier()),
                                $value instanceof SqlExpressionInterface 
                                    ? $value 
                                    : $this->expression($value)->decorate(Decorator::identifier())
                            )
                        ),
                        array_keys($columns),
                        $columns
                    )
                )
            );

        return $this;
    }

    protected function getPipeline(): iterable
    {
        return [
            KeywordEnum::INSERT => $this->expression()
                ->decorate(Decorator::wrapper(KeywordEnum::INSERT, ' ')),
            KeywordEnum::IGNORE => $this->expression()
                ->decorate(Decorator::wrapper(KeywordEnum::IGNORE, ' ')),
            KeywordEnum::DELAYED => $this->expression()
                ->decorate(Decorator::wrapper(KeywordEnum::DELAYED, ' ')),
            KeywordEnum::INTO => $this->expression()
                ->decorate(Decorator::wrapper(KeywordEnum::INTO, ' ')),
            KeywordEnum::COLUMNS => $this->expression()
                ->decorate(Decorator::wrapper(KeywordEnum::COLUMNS, '(', ')')),
            KeywordEnum::VALUES => $this->expression()
                ->decorate(Decorator::wrapper(KeywordEnum::VALUES, '(', ')')),
            KeywordEnum::SELECT => $this->expression(),
            KeywordEnum::ON_DUPLICATE_KEY => $this->expression(),
        ];
    }
    
}