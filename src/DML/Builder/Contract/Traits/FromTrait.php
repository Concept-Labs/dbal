<?php
namespace Concept\DBAL\DML\Builder\Contract\Traits;

use Concept\DBAL\DML\Expression\CharEnum;
use Concept\DBAL\DML\Expression\KeywordEnum;

trait FromTrait
{
    /**
     * {@inheritDoc}
     */
    public function from(...$tables): static
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
}