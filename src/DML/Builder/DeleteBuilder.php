<?php
namespace Concept\DBAL\DML\Builder;

use Concept\DBAL\DML\Builder\Contract\Traits\DeleteTrait;
use Concept\DBAL\DML\Builder\Contract\Traits\FromTrait;
use Concept\DBAL\DML\Expression\KeywordEnum;
use Concept\DBAL\DML\Expression\SqlExpressionInterface;


class DeleteBuilder extends SqlBuilder implements DeleteBuilderInterface
{
    use DeleteTrait;
    use FromTrait;
    

    /**
     * Get the pipeline of sections.
     * 
     * @return SqlExpressionInterface
     */
    protected function getPipeline(): SqlExpressionInterface
    {
        return $this->expression(
            $this->pipeSection(KeywordEnum::DELETE),
            $this->pipeSection(KeywordEnum::USING),
            $this->pipeSection(KeywordEnum::FROM),
            $this->pipeSection(KeywordEnum::JOIN, false),
            $this->pipeSection(KeywordEnum::WHERE),
            $this->pipeSection(KeywordEnum::ORDER_BY),
            $this->pipeSection(KeywordEnum::LIMIT),
            $this->pipeSection(KeywordEnum::LOCK),
            $this->pipeSection(KeywordEnum::RETURNING)
        )
        ->join(' ');
    }
}