<?php
namespace Concept\DBAL\DML\Builder\Contract\Traits;


use Concept\DBAL\DML\Expression\CharEnum;
use Concept\DBAL\DML\Expression\KeywordEnum;

trait UpdateTrait
{
    /**
     * {@inheritDoc}
     */
    public function update(string|array $table): static
    {
        $this->getSection(KeywordEnum::UPDATE)
            ->push(
                $this->aggregateAliasableList($table)
            );

        return $this;
    }


    /**
     * {@inheritDoc}
     */
    public function from(string|array ...$tables): static
    {
        if (empty($tables)) {
            throw new \InvalidArgumentException("FROM requires at least one table.");
        }

        $this->getSection(KeywordEnum::FROM)
            ->join(CharEnum::COMMA)
            ->push(
                $this->aggregateAliasableList(...$tables)
            );

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function set(array $values): static
    {
        $this->getSection(KeywordEnum::SET)
            ->join(CharEnum::COMMA)
            ->push(
                $this->expression(
                    ...array_map(
                        fn($column, $value) => $this->expression()->condition($column, '=', $value),
                        array_keys($values),
                        $values
                    )
                )
            );

        return $this;
    }


  
}