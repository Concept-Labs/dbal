<?php
namespace Concept\DBAL\DML\Builder;


use Concept\DBAL\DML\Builder\Contract\Traits\OrderByTrait;
use Concept\DBAL\DML\Builder\Contract\Traits\UpdateTrait;
use Concept\DBAL\Expression\KeywordEnum;
use Concept\DBAL\Expression\SqlExpressionInterface;

class UpdateBuilder extends SqlBuilder implements UpdateBuilderInterface
{
    use UpdateTrait;
    use OrderByTrait;

    /**
     * Get the pipeline of sections.
     * 
     * @return SqlExpressionInterface
     */
    protected function getPipeline(): SqlExpressionInterface
    {
        return $this->expression(
            $this->pipeSection(KeywordEnum::WITH),
            $this->pipeSection(KeywordEnum::UPDATE),
            $this->pipeSection(KeywordEnum::JOIN),
            $this->pipeSection(KeywordEnum::FROM),
            $this->pipeSection(KeywordEnum::SET),
            $this->pipeSection(KeywordEnum::WHERE),
            //$this->pipeSection(KeywordEnum::ORDER_BY), //use CTE(WITH) instead
            $this->pipeSection(KeywordEnum::LIMIT),
            $this->pipeSection(KeywordEnum::RETURNING),
        )
        ->join(' ');
    }

}