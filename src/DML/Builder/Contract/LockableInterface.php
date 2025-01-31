<?php
namespace Concept\DBAL\DML\Builder\Contract;

interface LockableInterface
{
/**
      * Lock the rows for update
      *
      * @return static
      */
    public function lockForUpdate(): static;
     
     /**
      * Lock the rows in share mode
      *
      * @return static
      */
    public function lockInShareMode(): static;

}