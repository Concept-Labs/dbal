<?php
namespace Concept\DBAL\DML\Builder;

use Concept\DBAL\DML\Builder\Contract\Traits\DeleteTrait;
use Concept\DBAL\DML\Expression\KeywordEnum;
use Concept\DBAL\DML\Expression\SqlExpressionInterface;

class DeleteBuilder extends SqlBuilder implements DeleteBuilderInterface
{
    use DeleteTrait;

    /**
     * Get the pipeline of sections.
     * 
     * @return SqlExpressionInterface
     */
    protected function getPipeline(): SqlExpressionInterface
    {
        return $this->expression(
            $this->pipeSection(KeywordEnum::DELETE),
            $this->pipeSection(KeywordEnum::USING),     // Для PostgreSQL
            $this->pipeSection(KeywordEnum::FROM),      // Деякі СУБД вимагають FROM після DELETE
            $this->pipeSection(KeywordEnum::JOIN),      // DELETE з JOIN (MySQL, SQL Server)
            $this->pipeSection(KeywordEnum::WHERE),     // Фільтр для безпечного видалення
            $this->pipeSection(KeywordEnum::ORDER_BY),  // Деколи потрібно для обмеження ORDER
            $this->pipeSection(KeywordEnum::LIMIT),     // MySQL, SQLite підтримують обмеження
            $this->pipeSection(KeywordEnum::RETURNING)  // PostgreSQL, Oracle повертають видалені дані
        )->join(' ');
    }

}