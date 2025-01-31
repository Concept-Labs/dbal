<?php
namespace Concept\DBAL\DML\Builder\Contract\Traits;

use Concept\DBAL\DML\Expression\KeywordEnum;

trait LockTrait
{
    /*
    * {@inheritDoc}
    */
    public function lockForUpdate(): static
    {
        $this->getSection(KeywordEnum::LOCK)
            ->push(KeywordEnum::FOR_UPDATE);
    
        return $this;
    }
    
    /*
    * {@inheritDoc}
    */
    public function lockInShareMode(): static
    {
        $this->getSection(KeywordEnum::LOCK)
            ->push(KeywordEnum::LOCK_IN_SHARE_MODE);
    
        return $this;
    }
}