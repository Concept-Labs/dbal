<?php
namespace Concept\DBAL\DML\Builder;

interface DeleteBuilderInterface extends SqlBuilderInterface
{
     /**
     * Initialize the query as an DELETE
     * 
     * @return static
     */
    public function delete(string $table): static;

    public function from(string|array $table): static;
    
}