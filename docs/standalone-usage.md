# Standalone Usage Guide

This guide demonstrates how to use Concept DBAL **without** a framework or dependency injection container, for standalone PHP applications.

## Overview

While Concept DBAL is designed primarily for use with the Singularity DI container and the Concept Ecosystem, it can also be used standalone. This requires manual instantiation of dependencies.

> **Note:** For production applications, we strongly recommend using the [Singularity DI container](dependency-injection.md) for automatic dependency management.

## Installation

```bash
composer require concept-labs/dbal
```

## Manual Setup

### 1. Create Database Connection

```php
<?php
require_once 'vendor/autoload.php';

use Concept\DBC\Pdo\PdoConnection;

// Create PDO connection
$connection = new PdoConnection(
    dsn: 'mysql:host=localhost;dbname=myapp;charset=utf8mb4',
    username: 'root',
    password: 'password',
    options: [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);
```

### 2. Create Expression Components

```php
use Concept\DBAL\DML\Expression\SqlExpressionFactory;
use Concept\DBAL\DML\Expression\SqlExpression;

// Create expression factory and prototype
$exprFactory = new SqlExpressionFactory();
$sqlExpression = $exprFactory->create();

// Set quote decorator for the expression
$sqlExpression->setQuoteDecorator(function($value) use ($connection) {
    return $connection->quote($value);
});
```

### 3. Create Builder Factories

```php
use Concept\DBAL\DML\Builder\Factory\RawBuilderFactory;
use Concept\DBAL\DML\Builder\Factory\SelectBuilderFactory;
use Concept\DBAL\DML\Builder\Factory\InsertBuilderFactory;
use Concept\DBAL\DML\Builder\Factory\UpdateBuilderFactory;
use Concept\DBAL\DML\Builder\Factory\DeleteBuilderFactory;

// Create builder factories
$rawBuilderFactory = new RawBuilderFactory($sqlExpression);
$selectBuilderFactory = new SelectBuilderFactory($sqlExpression);
$insertBuilderFactory = new InsertBuilderFactory($sqlExpression);
$updateBuilderFactory = new UpdateBuilderFactory($sqlExpression);
$deleteBuilderFactory = new DeleteBuilderFactory($sqlExpression);
```

### 4. Create DML Manager

```php
use Concept\DBAL\DML\DmlManager;

// Create DML manager
$dml = new DmlManager(
    $sqlExpression,
    $rawBuilderFactory,
    $selectBuilderFactory,
    $insertBuilderFactory,
    $updateBuilderFactory,
    $deleteBuilderFactory
);
```

### 5. Create DBAL Manager (Primary Service)

```php
use Concept\DBAL\DbalManager;
use Concept\DBAL\Dialect\MySQLDialect; // You'll need to create or use appropriate dialect

// Create dialect (database-specific)
$dialect = new MySQLDialect();

// Create DDL manager (if available)
// $ddl = new DdlManager(...);

// Create DBAL Manager - This is the main service
$dbal = new DbalManager(
    $connection,
    $dialect,
    $dml,
    $ddl ?? null
);
```

### 6. Use the DBAL Manager

```php
// Now you can use the DBAL manager
$users = $dbal->dml()
    ->select('*')
    ->from('users')
    ->where($dbal->dml()->expr()->condition('status', '=', 'active'))
    ->execute();

print_r($users);
```

## Complete Standalone Example

Here's a complete working example:

```php
<?php
require_once 'vendor/autoload.php';

use Concept\DBC\Pdo\PdoConnection;
use Concept\DBAL\DbalManager;
use Concept\DBAL\DML\DmlManager;
use Concept\DBAL\DML\Expression\SqlExpressionFactory;
use Concept\DBAL\DML\Builder\Factory\RawBuilderFactory;
use Concept\DBAL\DML\Builder\Factory\SelectBuilderFactory;
use Concept\DBAL\DML\Builder\Factory\InsertBuilderFactory;
use Concept\DBAL\DML\Builder\Factory\UpdateBuilderFactory;
use Concept\DBAL\DML\Builder\Factory\DeleteBuilderFactory;

// 1. Database connection
$connection = new PdoConnection(
    'mysql:host=localhost;dbname=testdb;charset=utf8mb4',
    'root',
    'password'
);

// 2. Expression setup
$exprFactory = new SqlExpressionFactory();
$sqlExpression = $exprFactory->create();
$sqlExpression->setQuoteDecorator(fn($v) => $connection->quote($v));

// 3. Builder factories
$rawBuilderFactory = new RawBuilderFactory($sqlExpression);
$selectBuilderFactory = new SelectBuilderFactory($sqlExpression);
$insertBuilderFactory = new InsertBuilderFactory($sqlExpression);
$updateBuilderFactory = new UpdateBuilderFactory($sqlExpression);
$deleteBuilderFactory = new DeleteBuilderFactory($sqlExpression);

// 4. DML manager
$dml = new DmlManager(
    $sqlExpression,
    $rawBuilderFactory,
    $selectBuilderFactory,
    $insertBuilderFactory,
    $updateBuilderFactory,
    $deleteBuilderFactory
);

// 5. DBAL manager (main service)
$dbal = new DbalManager(
    $connection,
    $dialect, // You need to provide appropriate dialect
    $dml,
    null // DDL manager optional
);

// 6. Use it!
try {
    // SELECT example
    $users = $dbal->dml()
        ->select('id', 'name', 'email')
        ->from('users')
        ->where($dbal->dml()->expr()->condition('status', '=', 'active'))
        ->orderBy('created_at', 'DESC')
        ->limit(10)
        ->execute();
    
    foreach ($users as $user) {
        echo "User: {$user['name']} ({$user['email']})\n";
    }
    
    // INSERT example
    $dbal->dml()
        ->insert('users')
        ->values([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ])
        ->execute();
    
    echo "User created successfully!\n";
    
    // UPDATE example
    $dbal->dml()
        ->update('users')
        ->set('status', 'inactive')
        ->where($dbal->dml()->expr()->condition('email', '=', 'john@example.com'))
        ->execute();
    
    echo "User updated successfully!\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

## Simplified Standalone Wrapper

For easier standalone use, create a wrapper class:

```php
<?php
namespace App\Database;

use Concept\DBAL\DbalManager;
use Concept\DBAL\DbalManagerInterface;
use Concept\DBC\Pdo\PdoConnection;
use Concept\DBAL\DML\DmlManager;
use Concept\DBAL\DML\Expression\SqlExpressionFactory;
use Concept\DBAL\DML\Builder\Factory\RawBuilderFactory;
use Concept\DBAL\DML\Builder\Factory\SelectBuilderFactory;
use Concept\DBAL\DML\Builder\Factory\InsertBuilderFactory;
use Concept\DBAL\DML\Builder\Factory\UpdateBuilderFactory;
use Concept\DBAL\DML\Builder\Factory\DeleteBuilderFactory;

class DatabaseFactory
{
    public static function create(
        string $dsn,
        string $username,
        string $password,
        array $options = []
    ): DbalManagerInterface {
        // Connection
        $connection = new PdoConnection($dsn, $username, $password, $options);
        
        // Expression
        $exprFactory = new SqlExpressionFactory();
        $sqlExpression = $exprFactory->create();
        $sqlExpression->setQuoteDecorator(fn($v) => $connection->quote($v));
        
        // Factories
        $rawBuilderFactory = new RawBuilderFactory($sqlExpression);
        $selectBuilderFactory = new SelectBuilderFactory($sqlExpression);
        $insertBuilderFactory = new InsertBuilderFactory($sqlExpression);
        $updateBuilderFactory = new UpdateBuilderFactory($sqlExpression);
        $deleteBuilderFactory = new DeleteBuilderFactory($sqlExpression);
        
        // DML
        $dml = new DmlManager(
            $sqlExpression,
            $rawBuilderFactory,
            $selectBuilderFactory,
            $insertBuilderFactory,
            $updateBuilderFactory,
            $deleteBuilderFactory
        );
        
        // DBAL (main service)
        return new DbalManager($connection, $dialect, $dml, null);
    }
}

// Usage:
$dbal = DatabaseFactory::create(
    'mysql:host=localhost;dbname=myapp',
    'root',
    'password'
);

$users = $dbal->dml()->select('*')->from('users')->execute();
```

## Standalone Repository Pattern

```php
<?php
namespace App\Repository;

use Concept\DBAL\DbalManagerInterface;

class UserRepository
{
    private DbalManagerInterface $dbal;
    
    public function __construct(DbalManagerInterface $dbal)
    {
        $this->dbal = $dbal;
    }
    
    public function findAll(): array
    {
        return $this->dbal->dml()
            ->select('*')
            ->from('users')
            ->execute();
    }
    
    public function findById(int $id): ?array
    {
        $results = $this->dbal->dml()
            ->select('*')
            ->from('users')
            ->where($this->dbal->dml()->expr()->condition('id', '=', $id))
            ->limit(1)
            ->execute();
        
        return $results[0] ?? null;
    }
    
    public function create(array $data): int
    {
        $this->dbal->dml()
            ->insert('users')
            ->values($data)
            ->execute();
        
        return (int) $this->dbal->getConnection()->lastInsertId();
    }
    
    public function update(int $id, array $data): bool
    {
        return $this->dbal->dml()
            ->update('users')
            ->set($data)
            ->where($this->dbal->dml()->expr()->condition('id', '=', $id))
            ->execute();
    }
    
    public function delete(int $id): bool
    {
        return $this->dbal->dml()
            ->delete('users')
            ->where($this->dbal->dml()->expr()->condition('id', '=', $id))
            ->execute();
    }
}

// Usage:
$dbal = DatabaseFactory::create(...);
$userRepo = new UserRepository($dbal);

$users = $userRepo->findAll();
$user = $userRepo->findById(1);
$newId = $userRepo->create(['name' => 'John', 'email' => 'john@example.com']);
```

## Environment Configuration

Use environment variables for configuration:

```php
<?php
// config.php
return [
    'database' => [
        'dsn' => getenv('DB_DSN') ?: 'mysql:host=localhost;dbname=myapp',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    ]
];

// index.php
$config = require 'config.php';

$dbal = DatabaseFactory::create(
    $config['database']['dsn'],
    $config['database']['username'],
    $config['database']['password'],
    $config['database']['options']
);
```

## Using with dotenv

```bash
composer require vlucas/phpdotenv
```

```php
<?php
require_once 'vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Create DBAL
$dbal = DatabaseFactory::create(
    $_ENV['DB_DSN'],
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD']
);
```

## Limitations of Standalone Usage

When using DBAL standalone (without Singularity DI):

⚠️ **Manual Dependency Management** - You must manually instantiate all dependencies
⚠️ **No Auto-wiring** - Cannot automatically resolve interface dependencies
⚠️ **More Boilerplate** - Requires more setup code
⚠️ **Lifecycle Management** - Must manually manage service lifecycles
⚠️ **Configuration** - Cannot use `concept.json` for automatic configuration

## Recommendation

For production applications, we strongly recommend using:

1. **[Singularity DI Container](dependency-injection.md)** - For automatic dependency management
2. **[Concept Ecosystem](concept-ecosystem.md)** - For seamless integration with other packages
3. **[Framework Integration](framework-integration.md)** - For modern framework support

## Next Steps

- **[Framework Integration](framework-integration.md)** - Integrate with Laravel, Symfony, etc.
- **[Dependency Injection](dependency-injection.md)** - Use Singularity for auto-wiring
- **[Quick Start](quickstart.md)** - Learn query building basics
- **[Examples](examples.md)** - See real-world usage patterns
