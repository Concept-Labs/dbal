# Concept DBAL
[![Concept](https://img.shields.io/badge/Concept-ecosystem-violet.svg)](https://github.com/Concept-Labs)
[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-8892BF.svg)](https://php.net/)

A **low-level, foundational tool** for building database abstractions in PHP 8.2+. Part of the [Concept Ecosystem](https://github.com/Concept-Labs), designed specifically for **Dependency Injection** using [Singularity Container](https://github.com/Concept-Labs/singularity).

> **⚠️ Important:** DBAL is **NOT an ORM or ActiveRecord** - it's a low-level query builder that you use to **BUILD** ORMs, ActiveRecord, Repositories, Collections, and other data patterns. Think of it as **LEGO blocks** for your data layer.

## What DBAL Is

- ✅ **Low-level query builder** - Programmatic SQL construction
- ✅ **Foundation for abstractions** - Build ORMs, ActiveRecord, Repositories on top
- ✅ **Type-safe primitives** - Building blocks for your data layer
- ✅ **Expression-based** - Built on [concept-labs/expression](https://github.com/Concept-Labs/expression)

## What DBAL Is Not

- ❌ **Not an ORM** - Doesn't map tables to objects (build your own!)
- ❌ **Not ActiveRecord** - No model classes (implement your own!)
- ❌ **Not a complete solution** - Provides primitives, not patterns (you choose the patterns!)

See **[Building on DBAL](docs/building-on-dbal.md)** for examples of building ActiveRecord, ORMs, Repositories, Collections, and more.

## Features

- 🎯 **Fluent Query Builder** - Intuitive, chainable API built on [concept-labs/expression](https://github.com/Concept-Labs/expression)
- 🔧 **Type-Safe** - Leverages PHP 8.2+ features for better type safety
- 🏗️ **Clean Architecture** - Interface-driven design with clear separation of concerns
- 💉 **DI-First** - Designed for dependency injection with Singularity container
- 🚀 **Performance** - Efficient query building with prototype pattern
- 🔌 **Extensible** - Easy to extend with custom builders and expressions
- 🌐 **Framework Agnostic** - Works standalone or integrates with modern frameworks
- 📦 **Ecosystem Integration** - Part of Concept-Labs packages working seamlessly together

## Quick Start

### Installation

```bash
composer require concept-labs/dbal
```

### With Dependency Injection (Recommended)

```php
use Concept\DBAL\DbalManagerInterface;

// Inject DbalManager - the primary service of this package
class UserRepository {
    public function __construct(
        private DbalManagerInterface $dbal
    ) {}
    
    public function findActiveUsers(): array {
        // Access DML operations through dbal manager
        return $this->dbal->dml()
            ->select('id', 'name', 'email')
            ->from('users')
            ->where($this->dbal->dml()->expr()->condition('status', '=', 'active'))
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->execute();
    }
}
```

### Standalone Usage

```php
use Concept\DBAL\DbalManager;
use Concept\DBC\Pdo\PdoConnection;
// ... other dependencies

// Setup connection
$connection = new PdoConnection(
    'mysql:host=localhost;dbname=myapp',
    'username',
    'password'
);

// Create DBAL manager manually (for standalone use)
$dbal = new DbalManager($connection, $dialect, $dml, $ddl);

// Use it
$users = $dbal->dml()->select('*')->from('users')->execute();
```

### Query Examples

```php
// SELECT Query
$users = $dbal->dml()->select('id', 'name', 'email')
    ->from('users')
    ->where($dbal->dml()->expr()->condition('age', '>', 18))
    ->orderBy('name')
    ->limit(10)
    ->execute();

// INSERT Query
$dbal->dml()->insert('users')
    ->values([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'created_at' => date('Y-m-d H:i:s')
    ])
    ->execute();

// UPDATE Query
$dbal->dml()->update('users')
    ->set('status', 'inactive')
    ->where($dbal->dml()->expr()->condition('last_login', '<', '2023-01-01'))
    ->execute();

// DELETE Query
$dbal->dml()->delete('users')
    ->where($dbal->dml()->expr()->condition('status', '=', 'deleted'))
    ->execute();
```

## Documentation

### 📚 Documentation Index

**Getting Started:**
- **[Introduction](docs/introduction.md)** - Package overview, Concept Ecosystem, what DBAL is/isn't
- **[Building on DBAL](docs/building-on-dbal.md)** - **Build ORMs, ActiveRecord, Repositories, Collections**
- **[Installation](docs/installation.md)** - Installation with DI and standalone setup
- **[Quick Start](docs/quickstart.md)** - Get started with query building
- **[Standalone Usage](docs/standalone-usage.md)** - Using DBAL without a framework
- **[Framework Integration](docs/framework-integration.md)** - Laravel, Symfony, Slim, and more

**Technical Guides:**
- **[Architecture](docs/architecture.md)** - Design patterns and Expression package integration
- **[Query Builders](docs/builders.md)** - Complete guide to query builders
- **[SQL Expressions](docs/expressions.md)** - Expression system from concept-labs/expression
- **[Dependency Injection](docs/dependency-injection.md)** - Integration with Singularity
- **[Concept Ecosystem](docs/concept-ecosystem.md)** - Integration with other Concept packages

**Reference & Examples:**
- **[API Reference](docs/api-reference.md)** - Complete API documentation
- **[Best Practices](docs/best-practices.md)** - Recommended patterns and practices
- **[Examples](docs/examples.md)** - Real-world usage examples
- **[Comparison](docs/comparison.md)** - How we compare to other solutions

## Why Concept DBAL?

This package is specifically designed as part of the **Concept Ecosystem** with these principles:

- **DI-First Architecture** - Built for dependency injection with Singularity container
- **Clean Code** - Interface-driven design promotes testability and maintainability
- **Type Safety** - Full use of PHP 8.2+ type system reduces runtime errors
- **Expression-Based** - Built on [concept-labs/expression](https://github.com/Concept-Labs/expression) for powerful query building
- **Ecosystem Integration** - Seamlessly works with other Concept-Labs packages
- **Framework Agnostic** - Use standalone or integrate with any modern PHP framework

## Part of Concept Ecosystem

**Concept DBAL** is a core component of the [Concept Ecosystem](https://github.com/Concept-Labs) and depends on:

### Core Dependencies

- **[concept-labs/expression](https://github.com/Concept-Labs/expression)** - Base expression language system
  - DBAL extends Expression to build SQL-specific expressions
  - Provides fluent, chainable expression building
  - Type-safe query component construction

- **[concept-labs/dbc-pdo](https://github.com/Concept-Labs/dbc-pdo)** - PDO Database Connection wrapper
  - Connection management and query execution
  - Transaction support
  - Prepared statement handling

- **[concept-labs/config](https://github.com/Concept-Labs/config)** - Configuration management
  - Database connection configuration
  - Environment-specific settings

- **[concept-labs/exception](https://github.com/Concept-Labs/exception)** - Exception handling
  - Structured error handling
  - Database-specific exceptions

### DI Container

- **[Singularity](https://github.com/Concept-Labs/singularity)** - Dependency Injection Container
  - Auto-wiring of all DBAL components
  - Service lifecycle management
  - `concept.json` configuration support

## Requirements

- PHP 8.2 or higher
- Composer for dependency management

## License

Apache License 2.0. See [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For issues, questions, or contributions, please visit our [GitHub repository](https://github.com/Concept-Labs/dbal).
