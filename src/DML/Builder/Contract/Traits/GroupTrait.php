<?php
namespace Concept\DBAL\DML\Builder\Contract\Traits;

use Concept\DBAL\Expression\KeywordEnum;

trait GroupTrait
{
    /**
     * {@inheritDoc}
     */
    public function groupBy(...$columns): static
    {
        $this->getSection(KeywordEnum::GROUP_BY)
            ->push(
                $this->aggregateAliasableList(...$columns)
            );

        return $this;
    }

    /**
     * @see groupBy()
     */
    public function group(...$columns): static
    {
        return $this->groupBy(...$columns);
    }
}