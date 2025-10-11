<?php
namespace Concept\DBAL\DML\Builder\Contract\Traits;

use Concept\DBAL\Expression\KeywordEnum;

trait DeleteTrait
{
    /**
     * {@inheritDoc}
     */
    public function delete(string $table): static
    {
        $this->getSection(KeywordEnum::DELETE)
            ->push($table);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    // public function from(string|array $table): static
    // {
    //     $this->getSection(KeywordEnum::FROM)
    //         ->push(
    //             match (true) {
    //                 is_string($table) => $this->expression()->identifier($table),
    //                 is_array($table) => $this->aggregateAliasableList($table),
    //             }
    //         );

    //     return $this;
    // }
}