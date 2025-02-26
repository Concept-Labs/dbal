<?php
namespace Concept\DBAL\DML\Builder;

use Concept\DBAL\DML\Expression\SqlExpressionInterface;
use Concept\DBAL\DML\Expression\KeywordEnum;


class RawBuilder extends SqlBuilder implements RawBuilderInterface
{

    /**
     * Add a RAW to the query
     * 
     * @param string $sql The raw SQL to add
     * 
     * @return static
     */
    public function raw(string|SqlExpressionInterface ...$sql): static
    {
        $this->getSection(KeywordEnum::RAW)
            ->reset()
            ->push(...$sql)
            ->wrap('(', ')')
            ->join(' ');

        return $this;
    }

    /**
     * Get the pipeline for the query
     * 
     * @return SqlExpressionInterface
     */
    protected function getPipeline(): SqlExpressionInterface
    {
        return $this->expression(
            $this->pipeSection(KeywordEnum::RAW, false),
        )->join(' ');
    }
    
}