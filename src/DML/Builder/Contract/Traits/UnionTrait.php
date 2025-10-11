<?php

namespace Concept\DBAL\DML\Builder\Contract\Traits;

use Concept\DBAL\Expression\KeywordEnum;
use Concept\DBAL\Expression\SqlExpressionInterface;

trait UnionTrait
{
    public function union(SqlExpressionInterface $query): static
    {
        $this->getSection(KeywordEnum::UNION)
            ->push(
                $this->expression()->keyword(KeywordEnum::UNION),
                $query
            );
        return $this;
    }

    // public function unionAll(SqlExpressionInterface $query): static
    // {
    //     $this->getSection(KeywordEnum::UNION)
    //         ->push(
    //             $this->expression()->keyword(KeywordEnum::UNION_ALL),
    //             $query
    //         );
    //     return $this;
    // }
}
