<?php
namespace Concept\DBAL;

use Concept\DBAL\DDL\DdlManagerInterface;
use Concept\DBAL\DML\DmlManagerInterface;

class DbalManager implements DbalManagerInterface
{

    public function __construct(private DmlManagerInterface $dml, private DdlManagerInterface $ddl)
    {
    }

    public function DML(): DmlManagerInterface
    {
        return $this->dml;
    }

    public function DDL(): DdlManagerInterface
    {
        return $this->ddl;
    }
}