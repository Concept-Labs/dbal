<?php

namespace Concept\DBAL\DML\Builder\Contract\Traits;

use Concept\DBAL\Expression\KeywordEnum;

trait WindowTrait
{


    public function window(string $function, string $partitionBy = null, string $orderBy = null): static
    {
        $windowExpression = $this->expression($function);

        if ($partitionBy) {
            $windowExpression->push(
                KeywordEnum::PARTITION_BY,
                $this->identifier($partitionBy)
            );
        }

        if ($orderBy) {
            $windowExpression->push(
                KeywordEnum::ORDER_BY,
                $this->identifier($orderBy)
            );
        }

        $this->getSection(KeywordEnum::SELECT)->push($windowExpression);

        return $this;
    }
}
