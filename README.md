# Concept DBAL

[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-8892BF.svg)](https://php.net/)

A modern, fluent Database Abstraction Layer (DBAL) for PHP 8.2+, part of the Concept Ecosystem. Built with clean architecture principles and designed to work seamlessly with [Singularity Dependency Injection Container](https://github.com/Concept-Labs/singularity).

## Features

- 🎯 **Fluent Query Builder** - Intuitive, chainable API for building SQL queries
- 🔧 **Type-Safe** - Leverages PHP 8.2+ features for better type safety
- 🏗️ **Clean Architecture** - Interface-driven design with clear separation of concerns
- 💉 **DI-Ready** - Native integration with Singularity dependency injection
- 🚀 **Performance** - Efficient query building with prototype pattern
- 🔌 **Extensible** - Easy to extend with custom builders and expressions
- 📦 **Lightweight** - Minimal dependencies, focused on query building

## Quick Start

### Installation

```bash
composer require concept-labs/dbal
```

### Basic Usage

```php
use Concept\DBAL\DML\DmlManagerInterface;

// Injected via Singularity DI
class UserRepository {
    public function __construct(
        private DmlManagerInterface $dml
    ) {}
    
    public function findActiveUsers(): array {
        return $this->dml
            ->select('id', 'name', 'email')
            ->from('users')
            ->where($this->dml->expr()->condition('status', '=', 'active'))
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->execute();
    }
}
```

### SELECT Query

```php
$query = $dml->select('id', 'name', 'email')
    ->from('users')
    ->where($dml->expr()->condition('age', '>', 18))
    ->orderBy('name')
    ->limit(10);
```

### INSERT Query

```php
$query = $dml->insert('users')
    ->values([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'created_at' => date('Y-m-d H:i:s')
    ]);
```

### UPDATE Query

```php
$query = $dml->update('users')
    ->set('status', 'inactive')
    ->where($dml->expr()->condition('last_login', '<', '2023-01-01'));
```

### DELETE Query

```php
$query = $dml->delete('users')
    ->where($dml->expr()->condition('status', '=', 'deleted'));
```

## Documentation

### 📚 Documentation Index

- **[Introduction](docs/introduction.md)** - Package overview and core concepts
- **[Installation](docs/installation.md)** - Detailed installation and setup guide
- **[Quick Start](docs/quickstart.md)** - Get started quickly with examples
- **[Architecture](docs/architecture.md)** - Deep dive into design and architecture
- **[Query Builders](docs/builders.md)** - Complete guide to query builders
- **[SQL Expressions](docs/expressions.md)** - Working with SQL expressions
- **[Dependency Injection](docs/dependency-injection.md)** - Integration with Singularity
- **[API Reference](docs/api-reference.md)** - Complete API documentation
- **[Best Practices](docs/best-practices.md)** - Recommended patterns and practices
- **[Examples](docs/examples.md)** - Real-world usage examples
- **[Comparison](docs/comparison.md)** - How we compare to other solutions

## Why Concept DBAL?

Concept DBAL is designed for modern PHP applications that value:

- **Clean Code** - Interface-driven design promotes testability and maintainability
- **Type Safety** - Full use of PHP 8.2+ type system reduces runtime errors
- **Flexibility** - Easy to extend and customize for your needs
- **DI Integration** - First-class support for dependency injection
- **Performance** - Efficient query building without unnecessary overhead

## Part of Concept Ecosystem

Concept DBAL is part of the larger [Concept Ecosystem](https://github.com/Concept-Labs), a collection of modern PHP packages designed to work together seamlessly. Built with dependency injection in mind, it integrates perfectly with:

- **[Singularity](https://github.com/Concept-Labs/singularity)** - Dependency Injection Container
- **[concept-labs/dbc-pdo](https://github.com/Concept-Labs/dbc-pdo)** - PDO Database Connection
- **[concept-labs/config](https://github.com/Concept-Labs/config)** - Configuration Management
- **[concept-labs/expression](https://github.com/Concept-Labs/expression)** - Expression Language
- **[concept-labs/exception](https://github.com/Concept-Labs/exception)** - Exception Handling

## Requirements

- PHP 8.2 or higher
- Composer for dependency management

## License

Apache License 2.0. See [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For issues, questions, or contributions, please visit our [GitHub repository](https://github.com/Concept-Labs/dbal).
