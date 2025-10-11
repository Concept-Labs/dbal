<?php
namespace Concept\DBAL\DDL;

interface DdlManagerFactoryInterface
{
    public function create(array $args = []): DdlManagerInterface;
}
