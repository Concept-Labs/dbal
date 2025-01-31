<?php
namespace Concept\DBAL\DML\Builder\Contract\Traits;

use Concept\DBAL\DML\Builder\SqlBuilderInterface;
use Concept\DBAL\DML\Expression\CharEnum;
use Concept\DBAL\DML\Expression\KeywordEnum;
use Concept\DBAL\DML\Expression\SqlExpressionInterface;
use Stringable;

trait SelectTrait
{
    /**
     * {@inheritDoc}
     */
    public function select(string|Stringable|SqlExpressionInterface|SqlBuilderInterface|array ...$columns): static
    {
        $this->getSection(KeywordEnum::SELECT)
            ->join(CharEnum::COMMA)
            ->push(
                // !$this->getSection(KeywordEnum::SELECT)->isEmpty()
                //     ? $this->expression(CharEnum::COMMA)
                //     : null,
                $this->aggregateAliasableList(...$columns)
            );

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function distinct(): static
    {
        $this->getSection(KeywordEnum::SELECT)
            ->unshift(KeywordEnum::DISTINCT);

        return $this;
    }

  
}