<?php
namespace Concept\DBAL\DML\Expression\Dialect;

abstract class DialectAdapter implements DialectAdapterInterface
{
    public function getIdentifierQuoteChar(): string
    {
        return '"';
    }

    public function getQuoteChar(): string
    {
        return "'";
    }

    public function getEscapeChar(): string
    {
        return '\\';
    }
}