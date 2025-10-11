<?php
namespace Concept\DBAL;

use Concept\DBAL\DML\DmlManagerInterface;

interface DbalManagerInterface
{

    public function dml(): DmlManagerInterface;
    //public function ddl(): DdlManagerInterface;

}