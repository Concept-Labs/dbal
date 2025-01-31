<?php
namespace Concept\DBAL\DML\Builder;

interface UpdateBuilderInterface extends SqlBuilderInterface
{
     /**
     * Initialize the query as an UPDATE
     * 
     * @return static
     */
    public function update(string $table): static;

    public function set(array $values): static;
    
}