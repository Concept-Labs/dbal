<?php

namespace Concept\DBAL\DML\Builder\Contract\Traits;

use Concept\DBAL\DML\Expression\KeywordEnum;
use Concept\DBAL\DML\Expression\SqlExpressionInterface;

trait CTETrait
{
    /**
     * {@inheritdoc}
     */
    public function with(string $name, SqlExpressionInterface $query): static
    {
        $this->getSection(KeywordEnum::WITH)
            ->push(
                $this->expression(
                    $this->identifier($name),
                    KeywordEnum::AS,
                    $query->wrap('(', ')')
                )
            );
        return $this;
    }
}
