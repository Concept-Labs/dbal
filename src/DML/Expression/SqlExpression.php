<?php

namespace Concept\DBAL\DML\Expression;

use Concept\DBAL\DML\Expression\Dialect\DialectAdapterInterface;
use Concept\Expression\Expression;

class SqlExpression extends Expression implements SqlExpressionInterface
{

    private ?DialectAdapterInterface $dialectAdapter = null;

    private final function ___diDialectAdapter(DialectAdapterInterface $dialectAdapter): void
    {
        $this->dialectAdapter = $dialectAdapter;
    }

    protected function getDialectAdapter(): DialectAdapterInterface
    {
        return $this->dialectAdapter;
    }

    public function escape(string $value): string
    {
        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function keyword(string $keyword): self
    {
        //@todo: check if keyword is valid
        return $this->push(strtoupper($keyword));
    }

    public function quoteIdentifier(string $identifier): string
    {
        $quoteChar = $this->getDialectAdapter()->getIdentifierQuoteChar();

        if (strpos($identifier, $quoteChar) !== false) {
            return $identifier;
        }

        if (strpos($identifier, CharEnum::DOT) === false) {
            return $quoteChar . $identifier . $quoteChar;
        }

        return $this->quoteQualifiedIdentifier($identifier);
    }

    public function quoteQualifiedIdentifier(string $identifier): string
    {
        return join(
            '.',
            array_map(
                fn($part) => $this->quoteIdentifier($part),
                explode('.', $identifier)
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function identifier(string $identifier): self
    {
        return $this->push($identifier)->decorate(
            fn($value) => $this->quoteIdentifier($value)
        );
    }

    public function value($value): self
    {
        return $this->push($value);
    }

    public function quote(string $value): self
    {
        return $this->push($value)->wrap(CharEnum::SINGLE_QUOTE);
    }

}
