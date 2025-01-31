<?php
namespace Concept\DBAL\DML\Builder\Contract;

interface BindableInterface
{
    /**
     * Bind a value to the query
     * 
     * @param string $name  The name of the binding
     * @param mixed  $value The value to bind
     * 
     * @return static
     */
    public function bind(array $bindings): static;

    /**
     * Get all the bindings
     * 
     * @return array
     */
    public function getBindings(): array;

    /**
     * Check if there are any bindings
     * 
     * @return bool
     */
    public function hasBindings(): bool;

    /**
     * Clear all bindings
     * 
     * @return static
     */
    public function clearBindings(): static;

    /**
     * Check if a binding exists
     * 
     * @param string $name The name of the binding
     * 
     * @return bool
     */
    public function hasBinding(string $name): bool;

    /**
     * Get a binding
     * 
     * @param string $name The name of the binding
     * 
     * @return mixed
     */
    public function getBinding(string $name): mixed;

    /**
     * Remove a binding
     * 
     * @param string $name The name of the binding
     * 
     * @return static
     */
    public function removeBinding(string $name): static;

}