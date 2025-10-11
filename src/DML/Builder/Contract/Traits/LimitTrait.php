<?php
namespace Concept\DBAL\DML\Builder\Contract\Traits;

use Concept\DBAL\Expression\KeywordEnum;
use Concept\DBAL\Expression\SqlExpressionInterface;

trait LimitTrait
{
    /**
     * {@inheritDoc}
     */
    public function limit(int $limit, ?int $offset = null): static
    {
        $this->getSection(KeywordEnum::LIMIT)
            ->push(
                $this->expression(
                    $limit,
                    $offset !== null ? $this->expression()->keyword(KeywordEnum::OFFSET) : null, 
                    $offset ?? null
                )->type(SqlExpressionInterface::TYPE_GROUP)
            );

        return $this;
    }
}