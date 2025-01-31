<?php
namespace Concept\DBAL\DML\Builder\Contract\Traits;

use Concept\DBAL\Exception\InvalidArgumentException;
use Stringable;

trait BindableTrait
{
    /**
     * @var array
     */
    protected array $bindings = [];

    /**
     * {@inheritDoc}
     */
    public function bind(array $bindings): static
    {
        foreach($bindings as $name => $value) {
            if (!is_string($name)) {
                throw new InvalidArgumentException("Invalid binding name. Must be a string.");
            }
            if (!is_scalar($value) && !is_null($value) && !$value instanceof Stringable) {
                throw new InvalidArgumentException("Invalid binding value. Must be scalar.");
            }

            $this->bindings[$name] = $value;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * {@inheritDoc}
     */
    public function clearBindings(): static
    {
        $this->bindings = [];

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasBindings(): bool
    {
        return !empty($this->bindings);
    }

    /**
     * {@inheritDoc}
     */
    public function getBinding(string $key): mixed
    {
        return $this->bindings[$key] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function setBinding(string $key, mixed $value): static
    {
        $this->bindings[$key] = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function removeBinding(string $key): static
    {
        unset($this->bindings[$key]);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasBinding(string $key): bool
    {
        return isset($this->bindings[$key]);
    }


}