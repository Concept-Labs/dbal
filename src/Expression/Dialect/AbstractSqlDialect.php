<?php
namespace Concept\DBAL\Expression\Dialect;

/**
 * Abstract base class for SQL dialects
 */
abstract class AbstractSqlDialect implements SqlDialectInterface
{
    protected array $supportedFeatures = [];

    /**
     * {@inheritDoc}
     */
    public function quoteValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_numeric($value)) {
            return (string)$value;
        }

        $quote = $this->getStringQuoteChar();
        return $quote . str_replace($quote, $quote . $quote, (string)$value) . $quote;
    }

    /**
     * {@inheritDoc}
     */
    public function quoteIdentifier(string $identifier): string
    {
        $quoteChar = $this->getIdentifierQuoteChar();

        // If already quoted, return as is
        if (strpos($identifier, $quoteChar) !== false) {
            return $identifier;
        }

        // Handle qualified identifiers (e.g., table.column)
        if (strpos($identifier, '.') !== false) {
            $parts = explode('.', $identifier);
            return implode('.', array_map(
                fn($part) => $quoteChar . $part . $quoteChar,
                $parts
            ));
        }

        return $quoteChar . $identifier . $quoteChar;
    }

    /**
     * {@inheritDoc}
     */
    public function getStringQuoteChar(): string
    {
        return "'";
    }

    /**
     * {@inheritDoc}
     */
    public function supportsFeature(string $feature): bool
    {
        return in_array($feature, $this->supportedFeatures);
    }

    /**
     * {@inheritDoc}
     */
    public function getLimitClause(int $limit, ?int $offset = null): string
    {
        if ($offset !== null) {
            return "LIMIT {$limit} OFFSET {$offset}";
        }
        return "LIMIT {$limit}";
    }

    /**
     * {@inheritDoc}
     */
    public function getIfNotExistsClause(): string
    {
        return 'IF NOT EXISTS';
    }

    /**
     * {@inheritDoc}
     */
    public function getIfExistsClause(): string
    {
        return 'IF EXISTS';
    }
}
