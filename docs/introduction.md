# Introduction to Concept DBAL

## Overview

Concept DBAL (Database Abstraction Layer) is a modern, fluent PHP library designed to simplify database operations through an intuitive query builder interface. It provides a clean, type-safe abstraction over SQL query construction, making database interactions more maintainable and less error-prone.

## What is DBAL?

DBAL stands for Database Abstraction Layer. Unlike a full ORM (Object-Relational Mapper), a DBAL focuses on:

- **Query Building** - Fluent, chainable API for constructing SQL queries
- **Abstraction** - Hide database-specific syntax differences
- **Type Safety** - Leverage PHP's type system for safer code
- **Flexibility** - Give you control over SQL while reducing boilerplate

Concept DBAL is not an ORM. It doesn't map database tables to objects or manage entity relationships. Instead, it provides a powerful, flexible layer for building and executing SQL queries programmatically.

## Core Concepts

### 1. DML Manager

The **DmlManager** (Data Manipulation Language Manager) is your main entry point for creating queries:

```php
$dml->select(...);  // Creates SELECT queries
$dml->insert(...);  // Creates INSERT queries
$dml->update(...);  // Creates UPDATE queries
$dml->delete(...);  // Creates DELETE queries
```

### 2. Query Builders

Each query type has a dedicated builder with methods specific to that operation:

- **SelectBuilder** - SELECT queries with joins, grouping, ordering, etc.
- **InsertBuilder** - INSERT queries with single or bulk inserts
- **UpdateBuilder** - UPDATE queries with joins and conditions
- **DeleteBuilder** - DELETE queries with conditions

### 3. SQL Expressions

The **SqlExpression** system provides building blocks for query components:

```php
$expr = $dml->expr();
$expr->condition('age', '>', 18);        // WHERE age > 18
$expr->in('status', ['active', 'pending']); // WHERE status IN (...)
$expr->like('name', 'John%');            // WHERE name LIKE 'John%'
```

### 4. Factory Pattern

Builders are created through factories, supporting the prototype pattern for efficient object creation:

```php
// Factories create builder instances
$selectBuilder = $selectBuilderFactory->create();
$insertBuilder = $insertBuilderFactory->create();
```

## Architecture Principles

### Interface-Driven Design

Every component is defined by an interface, promoting:
- **Testability** - Easy to mock dependencies
- **Flexibility** - Swap implementations as needed
- **Clean Contracts** - Clear API boundaries

### Dependency Injection

Designed from the ground up for DI containers, specifically [Singularity](https://github.com/Concept-Labs/singularity):

```php
// All dependencies injected via constructor
class DmlManager implements DmlManagerInterface {
    public function __construct(
        private SqlExpressionInterface $sqlExpressionPrototype,
        private RawBuilderFactoryInterface $rawBuilderFactory,
        private SelectBuilderFactoryInterface $selectBuilderFactory,
        // ... more factories
    ) {}
}
```

### Prototype Pattern

Builders use the prototype pattern for performance:
- Create a prototype instance once
- Clone it for each new query
- Avoid repeated initialization overhead

```php
// First call: create prototype
$this->selectBuilderPrototype = $this->selectBuilderFactory->create();

// Subsequent calls: clone prototype
return clone $this->selectBuilderPrototype;
```

### Fluent Interface

All builder methods return `$this`, enabling method chaining:

```php
$query = $dml->select('id', 'name')
    ->from('users')
    ->where($expr->condition('active', '=', true))
    ->orderBy('name')
    ->limit(10);
```

## Use Cases

Concept DBAL is ideal for:

### ✅ Good Fit
- **Custom Query Building** - When you need fine control over SQL
- **Legacy Database Integration** - Working with existing database schemas
- **Performance-Critical Operations** - Optimized queries without ORM overhead
- **Reporting and Analytics** - Complex queries with joins and aggregations
- **Microservices** - Lightweight database layer for services

### ❌ Not Ideal For
- **Simple CRUD Apps** - Full ORMs might be more convenient
- **Domain-Driven Design** - When you need rich domain models
- **Automatic Migrations** - No schema management built-in

## Key Benefits

### 1. Type Safety

Leverages PHP 8.2+ features:
```php
// Type hints prevent errors
public function select(string|array ...$columns): SelectBuilderInterface
public function where(SqlExpressionInterface $condition): static
```

### 2. Clean Architecture

Clear separation of concerns:
- **Interfaces** define contracts
- **Factories** create instances
- **Builders** construct queries
- **Expressions** represent SQL components

### 3. Testability

Easy to test with mocked dependencies:
```php
$mockDml = $this->createMock(DmlManagerInterface::class);
$mockDml->method('select')->willReturn($mockSelectBuilder);
```

### 4. Extensibility

Extend any component through interfaces:
```php
class CustomSelectBuilder extends SelectBuilder {
    public function customMethod(): static {
        // Add custom functionality
        return $this;
    }
}
```

### 5. Performance

- Efficient query building
- Prototype pattern reduces object creation overhead
- No magic methods or runtime reflection

## Comparison: DBAL vs ORM

| Feature | DBAL (Concept) | ORM (e.g., Doctrine) |
|---------|---------------|---------------------|
| **Control** | Full SQL control | Abstracted away |
| **Learning Curve** | Low (SQL knowledge) | High (ORM concepts) |
| **Performance** | Fast, minimal overhead | Slower, more overhead |
| **Use Case** | Query building | Domain modeling |
| **Flexibility** | High | Medium |
| **Type Safety** | Strong | Strong |
| **Relationships** | Manual | Automatic |
| **Migrations** | External tool | Built-in |

## Next Steps

- **[Installation Guide](installation.md)** - Set up Concept DBAL in your project
- **[Quick Start](quickstart.md)** - Build your first queries
- **[Architecture Guide](architecture.md)** - Deep dive into design patterns
- **[Query Builders](builders.md)** - Learn all builder methods
