<?php

namespace Concept\DBAL\DML\Builder\Contract\Traits;

use Concept\DBAL\DML\Expression\KeywordEnum;

trait ExplainTrait
{
    /**
     * {@inheritdoc}
     */
    public function explain(): static
    {
        $this->getSection(KeywordEnum::EXPLAIN)
            ->reset()
            ->push(KeywordEnum::EXPLAIN);

        return $this;
    }

    public function comment(string $comment): static
    {
        $this->getSection(KeywordEnum::COMMENT)
            ->push("-- $comment");

        return $this;
    }
}
