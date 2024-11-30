<?php
namespace Concept\DBAL\DML\Expression\Dialect;

interface DialectAdapterInterface
{
    
    public function getIdentifierQuoteChar(): string;

    public function getQuoteChar(): string;

    public function getEscapeChar(): string;

}