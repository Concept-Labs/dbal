<?php
namespace Concept\DBAL\Expression\Dialect;

/**
 * PostgreSQL Dialect Implementation
 */
class PostgreSqlDialect extends AbstractSqlDialect
{
    protected array $supportedFeatures = [
        'if_not_exists',
        'if_exists',
        'serial',
        'returning',
        'window_functions',
        'cte',
        'jsonb',
        'arrays',
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
        return 'postgresql';
    }

    /**
     * {@inheritDoc}
     */
    public function quoteValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }

        return parent::quoteValue($value);
    }
}
