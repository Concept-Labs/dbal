<?php
namespace Concept\DBAL\DML\Builder\Contract;

interface ExplainableInterface
{
    /**
     * Explain the query
     * 
     * @return static
     */
    public function explain(): static;

    public function comment(string $comment): static;
}