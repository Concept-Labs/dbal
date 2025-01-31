<?php
namespace Concept\DBAL\DML\Builder;

use Concept\DBAL\DML\Expression\SqlExpressionInterface;
use Concept\DBAL\DML\Expression\KeywordEnum;
use Stringable;
use Concept\DI\Factory\Attribute\Dependent;

#[Dependent]
class InsertBuilder extends SqlBuilder implements InsertBuilderInterface
{

    /**
     * Initialize the query as an INSERT
     * 
     * @return static
     */
    public function insert(?string $table = null): static
    {
        if ($table !== null) {
            $this->into($table);
        }

        return $this;
    }

    /**
     * Use the IGNORE keyword
     * 
     * @return static
     */
    public function ignore(): static
    {
        $this->getSection(KeywordEnum::IGNORE)->reset()->push('');

        return $this;
    }


    /**
     * Add a INTO to the query
     * 
     * @param string $table The table to insert into
     * 
     * @return static
     */
    public function into(string $table): static
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
     * @param string|Stringable|string[]|Stringable[] ...$columns The columns to add
     * 
     * @return static
     * 
     * @throws \InvalidArgumentException If the columns are empty
     */
    // public function columns(string|array|Stringable ...$columns): static
    // {
    //     $this->getSection(KeywordEnum::COLUMNS)->push(
    //         $this->expression()->join(', ')->push(...$this->normalizeArguments(...$columns))
    //     );
    //     return $this;
    // }

    /**
     * Add a VALUES to the query
     * Pass the values as arguments
     * Agruments must be arrays with the values to add
     * ([value, value, ...], [value, value, ...], ...)
     * 
     * @param array ...$values The values to add
     * 
     * @return static
     * 
     * @throws \InvalidArgumentException If the values are empty
     */
    public function values(...$values): static
    {
        $this->reset(KeywordEnum::SELECT);

        $columns = array_keys($values[0]);

        $this->getSection(KeywordEnum::COLUMNS)->reset()->push(
            $this->aggregateAliasableList(...$columns)->wrap('(', ')')
        );
        // $this->getSection(KeywordEnum::VALUES)->push(
        //     $this->aggregateAliasableList(...$values)->wrap('(', ')')
        // );

        foreach ($values as $_values) {

            $_values = array_values($_values);

            $this->getSection(KeywordEnum::VALUES)->push(
                $this->expression()->join(',')->wrap('(', ')')
                    ->push(
                        ...array_map(
                            function ($value) {
                                return $value instanceof SqlExpressionInterface 
                                    ? $value->wrap('(', ')') 
                                    : $this->expression()->quote($value);
                            },
                            $_values
                        )
                    )
            )->join(', ');
        }

        return $this;
    }
            

    /**
     * Add a SELECT to the query
     * 
     * @param SqlExpressionInterface $select The select query
     * 
     * @return static
     */
    public function fromSelect(string|Stringable|SqlExpressionInterface $select): static
    {
        $this->getSection(KeywordEnum::VALUES)->reset();
        $this->getSection(KeywordEnum::SELECT)->reset()->push($select);

        return $this;
    }


    /**
     * {@inheritDoc}
     */
    public function onDuplicateKey(array $columns): static
    {
        $this->getSection(KeywordEnum::ON_DUPLICATE)->reset();

        $this->getSection(KeywordEnum::ON_DUPLICATE)->push(
            $this->expression()->join(', ')->push(
                ...array_map(
                    function ($column, $value) {
                        return $this->expression()->join(' ')
                            ->push(
                                $this->expression()->identifier($column),
                                ' = ',
                                //$this->expression()->keyword(KeywordEnum::EQUAL),
                                $value instanceof SqlExpressionInterface
                                    ? $value
                                    : $this->expression()->quote($value)
                            );
                    },
                    array_keys($columns),
                    $columns
                )
            )
        );

        return $this;
    }

    public function returning(string|Stringable ...$columns): static
    {
        $this->getSection(KeywordEnum::RETURNING)
            ->push(
                $this->expression()->join(', ')->push(...$this->normalizeArguments(...$columns))
            );
        return $this;
    }

    // public function replace(): static
    // {
    //     $this->expression($this->expression()->keyword(KeywordEnum::REPLACE));
    // }

    protected function getPipeline(): SqlExpressionInterface
    {
        return $this->expression(
            $this->expression()->keyword(KeywordEnum::INSERT),
            $this->pipeSection(KeywordEnum::IGNORE),
            $this->pipeSection(KeywordEnum::INTO),
            $this->pipeSection(KeywordEnum::COLUMNS, false),
            $this->pipeSection(KeywordEnum::VALUES),
            $this->pipeSection(KeywordEnum::SELECT),
            $this->pipeSection(KeywordEnum::ON_DUPLICATE),
            $this->pipeSection(KeywordEnum::RETURNING),
        )->join(' ');
    }
    
}