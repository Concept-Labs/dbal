<?php
namespace Concept\DBAL;

use Concept\DBAL\DDL\DdlManagerInterface;
use Concept\DBAL\DML\DmlManagerInterface;
use Concept\DBC\ConnectionInterface;

class DbalManager implements DbalManagerInterface
{

    public function __construct(
        private ConnectionInterface $connection,
        private DialectInterface $dialect,
        private DmlManagerInterface $dml, 
        private DdlManagerInterface $ddl
        //, ...etc
    )
    {
    }

    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    public function getDialect(): DialectInterface
    {
        return $this->dialect;
    }

    public function getDmlManager(): DmlManagerInterface
    {
        return $this->dml;
    }

    public function getDdlManager(): DdlManagerInterface
    {
        return $this->ddl;
    }
}