<?php
namespace Concept\DBAL\Expression\Dialect;

/**
 * MySQL Dialect Implementation
 */
class MySqlDialect extends AbstractSqlDialect
{
    protected array $supportedFeatures = [
        'if_not_exists',
        'if_exists',
        'auto_increment',
        'unsigned',
        'fulltext_index',
        'spatial_index',
        'on_duplicate_key_update',
        'replace_into',
        'insert_ignore',
    ];

    /**
     * {@inheritDoc}
     */
    public function getIdentifierQuoteChar(): string
    {
        return '`';
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'mysql';
    }

    /**
     * {@inheritDoc}
     */
    public function getLimitClause(int $limit, ?int $offset = null): string
    {
        if ($offset !== null) {
            return "LIMIT {$offset}, {$limit}";
        }
        return "LIMIT {$limit}";
    }
}
