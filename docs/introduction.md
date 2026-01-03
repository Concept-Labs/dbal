# Introduction to Concept DBAL

## Overview

Concept DBAL (Database Abstraction Layer) is a **low-level, foundational tool** for building database abstractions in PHP 8.2+. It provides type-safe SQL query construction primitives that serve as building blocks for higher-level patterns.

## What is DBAL?

DBAL stands for Database Abstraction Layer. **Concept DBAL is NOT a high-level solution** - it's a tool for building them:

### What DBAL Is NOT

- ❌ **Not an ORM** - Doesn't map tables to objects
- ❌ **Not ActiveRecord** - Doesn't provide model classes
- ❌ **Not a complete data layer** - Doesn't include repositories, collections, or entities
- ❌ **Not high-level** - Doesn't abstract away SQL

### What DBAL IS

- ✅ **Low-level query builder** - Build SQL queries programmatically
- ✅ **Foundation for abstractions** - Use it to build ORMs, ActiveRecord, Repositories, Collections, etc.
- ✅ **Type-safe SQL construction** - Leverage PHP 8.2+ type system
- ✅ **Building blocks** - Primitives for your custom data layer

Think of DBAL as **LEGO blocks** - you use them to build whatever structure you need (ORM, ActiveRecord, Repository pattern, etc.).

## DBAL as a Foundation

```
┌─────────────────────────────────────────────┐
│  Your Layer: ORM, ActiveRecord, Repository  │
│      Collections, Specifications, etc.      │
│         ← YOU BUILD THIS                    │
├─────────────────────────────────────────────┤
│        Concept DBAL (Low-Level Tool)        │
│      Query Builder + SQL Expression         │
│         ← WE PROVIDE THIS                   │
├─────────────────────────────────────────────┤
│         Database Connection (PDO)           │
└─────────────────────────────────────────────┘
```

Concept DBAL provides the **primitives** - you build the **patterns** you need on top.

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

### ✅ Building Custom Abstractions
- Creating your own ORM tailored to your needs
- Implementing ActiveRecord pattern
- Building Repository layers
- Creating Collection classes
- Implementing any database pattern you need

### ✅ Foundation for Data Layers
- Base for custom query builders
- Foundation for CQRS implementations
- Building blocks for event sourcing
- Primitives for any data access pattern

### ✅ Low-Level Database Operations
- When you need full SQL control
- Performance-critical queries
- Complex SQL that ORMs struggle with
- Direct database manipulation

### ❌ Not Ideal For
- **Quick prototyping** - Use a full ORM instead (Doctrine, Eloquent)
- **Simple CRUD** - Use ActiveRecord implementations
- **When you need models** - Build them on top or use an ORM
- **High-level abstractions** - Build them yourself using DBAL as foundation

## Real-World Usage

### As a Foundation

```php
// You build this layer on top of DBAL
class UserRepository {
    public function __construct(private DbalManagerInterface $dbal) {}
    
    public function findActive(): Collection {
        // Use DBAL primitives
        $results = $this->dbal->dml()
            ->select('*')
            ->from('users')
            ->where($this->dbal->dml()->expr()->condition('status', '=', 'active'))
            ->execute();
        
        // Your collection implementation
        return new Collection($results);
    }
}
```

### Building Patterns

See **[Building on DBAL](building-on-dbal.md)** for complete examples of:
- ActiveRecord implementation
- ORM implementation  
- Repository pattern
- Collection classes
- Specification pattern
- And more...

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
