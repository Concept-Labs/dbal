<?php
namespace Concept\DBAL\DML\Builder;

use Concept\DBAL\DML\Builder\Contract\Traits\ConditionTrait;
use Concept\DBAL\DML\Builder\Contract\Traits\ExplainTrait;
use Concept\DBAL\DML\Builder\Contract\Traits\FromTrait;
use Concept\DBAL\DML\Builder\Contract\Traits\GroupTrait;
use Concept\DBAL\DML\Builder\Contract\Traits\JoinTrait;
use Concept\DBAL\DML\Builder\Contract\Traits\LockTrait;
use Concept\DBAL\DML\Builder\Contract\Traits\OrderByTrait;
use Concept\DBAL\DML\Builder\Contract\Traits\SelectTrait;
use Concept\DBAL\DML\Builder\Contract\Traits\UnionTrait;
use Concept\DBAL\DML\Expression\CharEnum;
use Concept\DBAL\DML\Expression\KeywordEnum;
use Concept\DBAL\DML\Expression\SqlExpressionInterface;


class SelectBuilder extends SqlBuilder implements SelectBuilderInterface
{
    use SelectTrait;
    use ConditionTrait;
    
    use JoinTrait;
    use FromTrait;
    use OrderByTrait;
    use GroupTrait;
    use UnionTrait;
    use LockTrait;
    use ExplainTrait;

    /**
     * Get table description.
     * 
     * @param string $table
     * 
     * @return static
     */
    public function describe(string $table): static
    {
        return $this->reset()
            ->select(CharEnum::ASTERISK)
            ->from('INFORMATION_SCHEMA.COLUMNS')
            ->where($this->expression()->condition('TABLE_NAME', '=', $table));
    }

    /**
     * Get the pipeline of sections.
     * 
     * @return SqlExpressionInterface
     */
    protected function getPipeline(): SqlExpressionInterface
    {
        return $this->expression(
            $this->pipeSection(KeywordEnum::DESCRIBE),
            $this->pipeSection(KeywordEnum::EXPLAIN),
            $this->pipeSection(KeywordEnum::WITH),
            $this->pipeSection(KeywordEnum::SELECT),
            $this->pipeSection(KeywordEnum::FROM),
            $this->pipeSection(KeywordEnum::JOIN, false),
            $this->pipeSection(KeywordEnum::WHERE),
            $this->pipeSection(KeywordEnum::GROUP_BY),
            $this->pipeSection(KeywordEnum::HAVING),
            $this->pipeSection(KeywordEnum::ORDER_BY),
            $this->pipeSection(KeywordEnum::LIMIT),
            $this->pipeSection(KeywordEnum::WINDOW),
            $this->pipeSection(KeywordEnum::UNION),
            $this->pipeSection(KeywordEnum::LOCK),
        )
        ->join(' ');
    }

}