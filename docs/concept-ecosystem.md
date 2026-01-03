# Concept Ecosystem Integration

This guide explains how Concept DBAL integrates with other packages in the [Concept Ecosystem](https://github.com/Concept-Labs).

## Overview

**Concept DBAL** is not a standalone package—it's a core component of the Concept Ecosystem, designed specifically for dependency injection and seamless integration with other Concept packages.

## Concept Ecosystem Architecture

```
┌─────────────────────────────────────────────────────┐
│          Concept Ecosystem                          │
├─────────────────────────────────────────────────────┤
│                                                     │
│  ┌──────────────┐      ┌──────────────┐           │
│  │  Singularity │◄─────┤  concept.json│           │
│  │  DI Container│      │  Config      │           │
│  └──────┬───────┘      └──────────────┘           │
│         │ Auto-wire                                │
│         │                                          │
│  ┌──────▼──────────────────────────────────────┐  │
│  │         DbalManager (Primary Service)       │  │
│  │              Concept\DBAL                   │  │
│  └─────────┬────────────────────┬───────────────┘  │
│            │                    │                  │
│     ┌──────▼────────┐    ┌─────▼───────┐          │
│     │  DmlManager   │    │ DdlManager  │          │
│     │  (Queries)    │    │ (Schema)    │          │
│     └──────┬────────┘    └─────────────┘          │
│            │                                       │
│     ┌──────▼────────────────┐                     │
│     │   SqlExpression       │                     │
│     │   (extends concept-   │                     │
│     │    labs/expression)   │                     │
│     └──────┬────────────────┘                     │
│            │                                       │
│  ┌─────────▼──────────────────────────────────┐   │
│  │        concept-labs/expression             │   │
│  │        Base Expression System              │   │
│  └────────────────────────────────────────────┘   │
│                                                    │
│  ┌────────────────────────────────────────────┐   │
│  │        concept-labs/dbc-pdo                │   │
│  │        Connection & Execution              │   │
│  └────────────────────────────────────────────┘   │
│                                                    │
│  ┌────────────────────────────────────────────┐   │
│  │        concept-labs/config                 │   │
│  │        Configuration Management            │   │
│  └────────────────────────────────────────────┘   │
│                                                    │
│  ┌────────────────────────────────────────────┐   │
│  │        concept-labs/exception              │   │
│  │        Exception Handling                  │   │
│  └────────────────────────────────────────────┘   │
│                                                    │
└─────────────────────────────────────────────────────┘
```

## Core Dependencies

### 1. concept-labs/expression

**Repository:** https://github.com/Concept-Labs/expression

**Purpose:** Base expression language system that DBAL extends for SQL-specific functionality.

#### What DBAL Inherits from Expression

```php
namespace Concept\DBAL\DML\Expression;

use Concept\Expression\ExpressionInterface;

interface SqlExpressionInterface extends ExpressionInterface
{
    // SQL-specific methods extend base Expression functionality
}
```

#### Expression Package Abilities

The expression package provides:

- **Fluent Builder Pattern** - Chainable expression building
- **Type System** - Expression type tracking (keyword, identifier, value, etc.)
- **Composition** - Combine expressions into complex structures
- **Serialization** - Convert expressions to strings
- **Immutability** - Expressions are immutable by design

#### How DBAL Extends Expression

DBAL adds SQL-specific capabilities on top of the base expression system:

```php
use Concept\Expression\Expression; // Base class
use Concept\DBAL\DML\Expression\SqlExpression; // DBAL extension

class SqlExpression extends Expression implements SqlExpressionInterface
{
    // SQL-specific types
    const TYPE_IDENTIFIER = 'identifier';  // Table/column names
    const TYPE_CONDITION = 'condition';    // WHERE conditions
    const TYPE_OPERATOR = 'operator';      // SQL operators
    
    // SQL-specific methods
    public function identifier(string $name): static;
    public function condition($left, $op, $right): static;
    public function in($column, array $values): static;
    public function like($column, $pattern): static;
    
    // Aggregate functions
    public function count($column, $alias = null): static;
    public function sum($column, $alias = null): static;
    public function avg($column, $alias = null): static;
}
```

#### Expression Usage Examples

```php
// Basic expression building
$expr = $dbal->dml()->expr();

// Simple condition (uses Expression base + SQL extension)
$condition = $expr->condition('age', '>', 18);

// Complex expression composition (Expression base functionality)
$complex = $expr->group(
    $expr->condition('age', '>', 18),
    'AND',
    $expr->condition('status', '=', 'active')
);

// SQL-specific: Aggregate (DBAL extension)
$count = $expr->count('*', 'total');

// SQL-specific: IN clause (DBAL extension)
$inClause = $expr->in('status', ['active', 'pending']);
```

### 2. concept-labs/dbc-pdo

**Repository:** https://github.com/Concept-Labs/dbc-pdo

**Purpose:** PDO database connection wrapper providing connection management and query execution.

#### Features

- **Connection Management** - Handle database connections
- **Query Execution** - Execute prepared statements
- **Transaction Support** - BEGIN, COMMIT, ROLLBACK
- **Parameter Binding** - Secure parameter binding
- **Result Fetching** - Fetch query results

#### Integration with DBAL

```php
use Concept\DBC\ConnectionInterface;
use Concept\DBC\Pdo\PdoConnection;

// DbalManager uses ConnectionInterface
class DbalManager implements DbalManagerInterface
{
    public function __construct(
        private ConnectionInterface $connection,
        // ... other dependencies
    ) {}
    
    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }
}

// Usage
$connection = new PdoConnection($dsn, $user, $pass);
$dbal = new DbalManager($connection, ...);

// Execute queries
$results = $dbal->dml()->select('*')->from('users')->execute();

// Access connection for transactions
$conn = $dbal->getConnection();
$conn->beginTransaction();
try {
    // Multiple queries...
    $conn->commit();
} catch (\Exception $e) {
    $conn->rollBack();
    throw $e;
}
```

### 3. concept-labs/config

**Repository:** https://github.com/Concept-Labs/config

**Purpose:** Configuration management for database and application settings.

#### Integration

```php
use Concept\Config\ConfigInterface;

class DatabaseConfig
{
    public function __construct(
        private ConfigInterface $config
    ) {}
    
    public function getDsn(): string
    {
        return $this->config->get('database.dsn');
    }
    
    public function getCredentials(): array
    {
        return [
            'username' => $this->config->get('database.username'),
            'password' => $this->config->get('database.password'),
        ];
    }
}
```

### 4. concept-labs/exception

**Repository:** https://github.com/Concept-Labs/exception

**Purpose:** Structured exception handling for database operations.

#### Exception Hierarchy

```php
use Concept\Exception\ExceptionInterface;
use Concept\DBAL\Exception\DBALExceptionInterface;

// DBAL exceptions extend Concept exception system
interface DBALExceptionInterface extends ExceptionInterface
{
    // DBAL-specific exception contract
}

class DBALException extends RuntimeException implements DBALExceptionInterface
{
    // Implementation
}

// Usage
try {
    $dbal->dml()->select('*')->from('users')->execute();
} catch (DBALException $e) {
    // Handle DBAL-specific errors
    error_log($e->getMessage());
}
```

### 5. Singularity DI Container

**Repository:** https://github.com/Concept-Labs/singularity

**Purpose:** Dependency injection container with auto-wiring.

#### concept.json Configuration

DBAL includes `concept.json` for automatic service registration:

```json
{
    "singularity": {
        "package": {
            "concept-labs/dbal": {
                "preference": {
                    "Concept\\DBAL\\DbalManagerInterface": {
                        "class": "Concept\\DBAL\\DbalManager"
                    },
                    "Concept\\DBAL\\DML\\DmlManagerInterface": {
                        "class": "Concept\\DBAL\\DML\\DmlManager"
                    },
                    "Concept\\DBAL\\DML\\Expression\\SqlExpressionInterface": {
                        "class": "Concept\\DBAL\\DML\\Expression\\SqlExpression"
                    }
                    // ... all interface->class mappings
                }
            }
        }
    }
}
```

#### Auto-wiring Example

```php
use Concept\Singularity\Container;
use Concept\DBAL\DbalManagerInterface;

// Create container
$container = new Container();

// Automatically resolves ALL dependencies
$dbal = $container->get(DbalManagerInterface::class);

// Use it
$users = $dbal->dml()->select('*')->from('users')->execute();
```

## Integration Patterns

### Full Ecosystem Setup

```php
<?php
require_once 'vendor/autoload.php';

use Concept\Singularity\Container;
use Concept\DBAL\DbalManagerInterface;
use Concept\Config\ConfigInterface;

// 1. Create Singularity container
$container = new Container();

// 2. Register config
$container->bind(ConfigInterface::class, function() {
    return new Config(['database' => [
        'dsn' => getenv('DB_DSN'),
        'username' => getenv('DB_USER'),
        'password' => getenv('DB_PASS'),
    ]]);
});

// 3. Get DBAL (auto-wired with all dependencies)
$dbal = $container->get(DbalManagerInterface::class);

// 4. Inject into your services
class UserService
{
    public function __construct(
        private DbalManagerInterface $dbal,
        private ConfigInterface $config
    ) {}
    
    public function getUsers(): array
    {
        return $this->dbal->dml()
            ->select('*')
            ->from('users')
            ->limit($this->config->get('pagination.limit'))
            ->execute();
    }
}

$userService = $container->get(UserService::class);
```

### Cross-Package Communication

```php
// Service using multiple Concept packages
class ReportService
{
    public function __construct(
        private DbalManagerInterface $dbal,          // concept-labs/dbal
        private ConfigInterface $config,             // concept-labs/config
        private LoggerInterface $logger,             // concept-labs/logger
        private CacheInterface $cache                // concept-labs/cache
    ) {}
    
    public function generateReport(): array
    {
        $cacheKey = $this->config->get('reports.cache_key');
        
        // Try cache first
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        try {
            // Query database
            $data = $this->dbal->dml()
                ->select('*')
                ->from('reports')
                ->execute();
            
            // Cache results
            $this->cache->set($cacheKey, $data, 3600);
            
            return $data;
            
        } catch (DBALException $e) {
            $this->logger->error('Report generation failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
```

## Benefits of Ecosystem Integration

### 1. Automatic Dependency Management

Singularity auto-wires all dependencies based on type hints:

```php
// No manual instantiation needed
$userRepo = $container->get(UserRepository::class);
// All dependencies automatically injected
```

### 2. Consistent Configuration

All packages use the same configuration system:

```php
// config.php
return [
    'database' => [...],
    'cache' => [...],
    'logging' => [...],
];

// Accessible from any service
$this->config->get('database.dsn');
```

### 3. Unified Exception Handling

Consistent exception hierarchy across all packages:

```php
try {
    $dbal->dml()->select('*')->from('users')->execute();
} catch (ConceptException $e) {
    // Handle any Concept ecosystem exception
}
```

### 4. Shared Patterns

All packages follow the same architectural patterns:
- Interface-driven design
- Dependency injection
- Factory pattern
- Type safety

## Expression System Deep Dive

### Base Expression Capabilities

From `concept-labs/expression`:

```php
// Creating expressions
$expr = new Expression();

// Type system
$expr->setType(Expression::TYPE_KEYWORD);
$type = $expr->getType();

// Composition
$expr->push($child1, $child2);
$expr->join(' ');

// Serialization
$sql = (string) $expr;
```

### SQL-Specific Extensions

DBAL adds SQL functionality:

```php
// Identifiers (table/column names)
$expr->identifier('users.id');  // Escapes as needed

// Values (with quoting)
$expr->value('John');  // Quoted and escaped

// Conditions
$expr->condition('age', '>', 18);

// Complex SQL structures
$expr->group(
    $expr->condition('a', '=', 1),
    'AND',
    $expr->condition('b', '=', 2)
);

// Aggregates
$expr->count('*', 'total');
$expr->sum('amount', 'total_amount');
```

### Expression Pipeline

How expressions flow through DBAL:

```
1. Create Expression
   ↓
2. Add SQL-specific components (conditions, identifiers, etc.)
   ↓
3. Compose into query builder
   ↓
4. Convert to SQL string
   ↓
5. Execute via DBC connection
   ↓
6. Return results
```

## Complete Ecosystem Example

```php
<?php
namespace App;

use Concept\Singularity\Container;
use Concept\DBAL\DbalManagerInterface;
use Concept\Config\ConfigInterface;
use Concept\Logger\LoggerInterface;
use Concept\Cache\CacheInterface;

// 1. Bootstrap container
$container = new Container();

// 2. All services auto-wired
class Application
{
    public function __construct(
        private DbalManagerInterface $dbal,
        private ConfigInterface $config,
        private LoggerInterface $logger,
        private CacheInterface $cache
    ) {}
    
    public function run(): void
    {
        $this->logger->info('Application started');
        
        // Use DBAL
        $users = $this->dbal->dml()
            ->select('*')
            ->from('users')
            ->where($this->dbal->dml()->expr()->condition(
                'status', 
                '=', 
                $this->config->get('user.default_status')
            ))
            ->execute();
        
        // Cache results
        $this->cache->set('active_users', $users, 3600);
        
        $this->logger->info('Found users', ['count' => count($users)]);
    }
}

// 3. Run application
$app = $container->get(Application::class);
$app->run();
```

## Next Steps

- **[Dependency Injection](dependency-injection.md)** - Deep dive into Singularity
- **[Architecture](architecture.md)** - Understanding DBAL design
- **[Installation](installation.md)** - Setting up the ecosystem
- **[Expression Guide](expressions.md)** - Mastering SQL expressions
