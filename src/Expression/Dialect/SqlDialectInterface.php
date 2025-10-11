<?php
namespace Concept\DBAL\Expression\Dialect;

/**
 * SQL Dialect Interface
 * 
 * Defines methods for handling database-specific SQL syntax differences
 */
interface SqlDialectInterface
{
    /**
     * Quote a value for SQL
     * 
     * @param mixed $value The value to quote
     * @return string The quoted value
     */
    public function quoteValue(mixed $value): string;

    /**
     * Quote an identifier (table/column name)
     * 
     * @param string $identifier The identifier to quote
     * @return string The quoted identifier
     */
    public function quoteIdentifier(string $identifier): string;

    /**
     * Get the identifier quote character
     * 
     * @return string The quote character (e.g., ` for MySQL, " for PostgreSQL)
     */
    public function getIdentifierQuoteChar(): string;

    /**
     * Get the string quote character
     * 
     * @return string The quote character (usually ')
     */
    public function getStringQuoteChar(): string;

    /**
     * Get the dialect name
     * 
     * @return string The dialect name (e.g., 'mysql', 'postgresql', 'sqlite')
     */
    public function getName(): string;

    /**
     * Check if a feature is supported
     * 
     * @param string $feature The feature name
     * @return bool True if supported
     */
    public function supportsFeature(string $feature): bool;

    /**
     * Get the LIMIT syntax for this dialect
     * 
     * @param int $limit The limit value
     * @param int|null $offset The offset value
     * @return string The LIMIT clause
     */
    public function getLimitClause(int $limit, ?int $offset = null): string;

    /**
     * Get IF NOT EXISTS syntax for CREATE TABLE
     * 
     * @return string The IF NOT EXISTS clause
     */
    public function getIfNotExistsClause(): string;

    /**
     * Get IF EXISTS syntax for DROP TABLE
     * 
     * @return string The IF EXISTS clause
     */
    public function getIfExistsClause(): string;
}
