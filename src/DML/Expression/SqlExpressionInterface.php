<?php
namespace Concept\DBAL\DML\Expression;

use Concept\Expression\ExpressionInterface;

interface SqlExpressionInterface extends ExpressionInterface
{
    /**
     * @param string $keyword
     * @return $this
     */
    public function keyword(string $keyword): self;

    /**
     * @param string $identifier
     * @return $this
     */
    public function identifier(string $identifier): self;

    /**
     * @param mixed $value
     * @return $this
     */
    public function value($value): self;

    /**
     * @param string $value
     * @return $this
     */
    public function quote(string $value): self;
}