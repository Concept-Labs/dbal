<?php
namespace Concept\DBAL\Expression\Dialect;

/**
 * SQLite Dialect Implementation
 */
class SqliteDialect extends AbstractSqlDialect
{
    protected array $supportedFeatures = [
        'if_not_exists',
        'if_exists',
        'autoincrement',
        'cte',
    ];

    /**
     * {@inheritDoc}
     */
    public function getIdentifierQuoteChar(): string
    {
        return '"';
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'sqlite';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return parent::quoteValue($value);
    }
}
