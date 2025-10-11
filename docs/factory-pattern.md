# Factory Pattern in DBAL

This document explains the factory pattern implementation used throughout the DBAL package.

## Table of Contents

1. [Overview](#overview)
2. [Why Factory Pattern?](#why-factory-pattern)
3. [Implementation](#implementation)
4. [Dependency Injection](#dependency-injection)
5. [Examples](#examples)

## Overview

The DBAL package uses the Factory Pattern extensively to create builder instances. This pattern provides:

- **Loose coupling**: Classes depend on interfaces, not concrete implementations
- **Flexibility**: Easy to swap implementations
- **Testability**: Simple to mock factories in tests
- **Consistency**: Standardized way to create objects

## Why Factory Pattern?

### Problem Without Factories

```php
// Tightly coupled - hard to test and maintain
class DmlManager
{
    public function select(...$columns)
    {
        $builder = new SelectBuilder($connection, $expression);
        return $builder->select(...$columns);
    }
}
```

### Solution With Factories

```php
// Loosely coupled - easy to test and maintain
class DmlManager
{
    public function __construct(
        private SelectBuilderFactoryInterface $selectFactory
    ) {}

    public function select(...$columns)
    {
        $builder = $this->selectFactory->create();
        return $builder->select(...$columns);
    }
}
```

## Implementation

### Factory Interface

Every builder has a corresponding factory interface:

```php
namespace Concept\DBAL\DML\Builder\Factory;

use Concept\DBAL\DML\Builder\SelectBuilderInterface;

interface SelectBuilderFactoryInterface
{
    /**
     * Create a new SelectBuilder instance
     * 
     * @param array $args Optional constructor arguments
     * @return SelectBuilderInterface
     */
    public function create(array $args = []): SelectBuilderInterface;
}
```

### Factory Implementation

Factories extend `ServiceFactory` from the Singularity package:

```php
namespace Concept\DBAL\DML\Builder\Factory;

use Concept\DBAL\DML\Builder\SelectBuilderInterface;
use Concept\Singularity\Factory\ServiceFactory;

class SelectBuilderFactory extends ServiceFactory implements SelectBuilderFactoryInterface
{
    public function create(array $args = []): SelectBuilderInterface
    {
        return $this->createService(SelectBuilderInterface::class, $args);
    }
}
```

### Builder Usage

Managers use factories to create builders:

```php
namespace Concept\DBAL\DML;

class DmlManager implements DmlManagerInterface
{
    private ?SelectBuilderInterface $selectBuilderPrototype = null;

    public function __construct(
        private SelectBuilderFactoryInterface $selectBuilderFactory,
        // ... other dependencies
    ) {}

    public function select(...$columns): SelectBuilderInterface
    {
        return $this->getSelectBuilder()->select(...$columns);
    }

    protected function getSelectBuilder(): SelectBuilderInterface
    {
        if (null === $this->selectBuilderPrototype) {
            $this->selectBuilderPrototype = $this->selectBuilderFactory->create();
        }

        return clone $this->selectBuilderPrototype;
    }
}
```

## Dependency Injection

### Configuration

Factories are configured in `concept.json`:

```json
{
    "singularity": {
        "package": {
            "concept-labs/dbal": {
                "preference": {
                    "Concept\\DBAL\\DML\\Builder\\Factory\\SelectBuilderFactoryInterface": {
                        "class": "Concept\\DBAL\\DML\\Builder\\Factory\\SelectBuilderFactory"
                    },
                    "Concept\\DBAL\\DML\\Builder\\SelectBuilderInterface": {
                        "class": "Concept\\DBAL\\DML\\Builder\\SelectBuilder"
                    }
                }
            }
        }
    }
}
```

### Container Resolution

The Singularity container automatically:

1. Resolves factory interfaces to concrete implementations
2. Injects dependencies into factory constructors
3. Creates builder instances through factories

## Examples

### DML Factories

#### Select Builder Factory

```php
// Factory creates SELECT query builders
$selectFactory = $container->get(SelectBuilderFactoryInterface::class);
$builder = $selectFactory->create();

$result = $builder->select('*')
    ->from('users')
    ->execute();
```

#### Insert Builder Factory

```php
// Factory creates INSERT query builders
$insertFactory = $container->get(InsertBuilderFactoryInterface::class);
$builder = $insertFactory->create();

$builder->insert('users')
    ->values(['name' => 'John', 'email' => 'john@example.com'])
    ->execute();
```

#### Update Builder Factory

```php
// Factory creates UPDATE query builders
$updateFactory = $container->get(UpdateBuilderFactoryInterface::class);
$builder = $updateFactory->create();

$builder->update('users')
    ->set('status', 'active')
    ->where('id', '=', 1)
    ->execute();
```

#### Delete Builder Factory

```php
// Factory creates DELETE query builders
$deleteFactory = $container->get(DeleteBuilderFactoryInterface::class);
$builder = $deleteFactory->create();

$builder->delete('users')
    ->where('status', '=', 'deleted')
    ->execute();
```

### DDL Factories

#### Create Table Builder Factory

```php
// Factory creates CREATE TABLE builders
$createFactory = $container->get(CreateTableBuilderFactoryInterface::class);
$builder = $createFactory->create();

$builder->createTable('users')
    ->addColumn('id', 'INT', ['AUTO_INCREMENT'])
    ->addColumn('name', 'VARCHAR(255)')
    ->primaryKey('id')
    ->execute();
```

#### Alter Table Builder Factory

```php
// Factory creates ALTER TABLE builders
$alterFactory = $container->get(AlterTableBuilderFactoryInterface::class);
$builder = $alterFactory->create();

$builder->alterTable('users')
    ->addColumn('phone', 'VARCHAR(20)')
    ->execute();
```

### Manager Factories

#### DML Manager Factory

```php
// Factory creates DML manager instances
$dmlFactory = $container->get(DmlManagerFactoryInterface::class);
$dml = $dmlFactory->create();

$dml->select('*')->from('users')->execute();
```

#### DDL Manager Factory

```php
// Factory creates DDL manager instances
$ddlFactory = $container->get(DdlManagerFactoryInterface::class);
$ddl = $ddlFactory->create();

$ddl->createTable('users')->/* ... */->execute();
```

## Prototype Pattern

The DBAL uses a combination of Factory and Prototype patterns:

```php
protected function getSelectBuilder(): SelectBuilderInterface
{
    // Create prototype once
    if (null === $this->selectBuilderPrototype) {
        $this->selectBuilderPrototype = $this->selectBuilderFactory->create();
    }

    // Clone for each use (Prototype pattern)
    return clone $this->selectBuilderPrototype;
}
```

### Benefits of Prototype Pattern

1. **Performance**: Builder created only once, then cloned
2. **Isolation**: Each query gets its own builder instance
3. **Memory**: Shared dependencies through prototype

## Testing with Factories

### Mocking Factories

```php
use PHPUnit\Framework\TestCase;

class DmlManagerTest extends TestCase
{
    public function testSelect()
    {
        // Mock the factory
        $selectFactory = $this->createMock(SelectBuilderFactoryInterface::class);
        
        // Mock the builder
        $selectBuilder = $this->createMock(SelectBuilderInterface::class);
        
        // Factory returns mocked builder
        $selectFactory->method('create')
            ->willReturn($selectBuilder);
        
        // Builder returns itself for chaining
        $selectBuilder->method('select')
            ->willReturn($selectBuilder);
        
        // Create manager with mocked factory
        $dml = new DmlManager(
            connection: $connection,
            sqlExpressionPrototype: $expression,
            selectBuilderFactory: $selectFactory,
            // ... other factories
        );
        
        // Test
        $result = $dml->select('*');
        $this->assertSame($selectBuilder, $result);
    }
}
```

### Factory Spies

```php
public function testFactoryUsage()
{
    $selectFactory = $this->createMock(SelectBuilderFactoryInterface::class);
    
    // Expect factory to be called once
    $selectFactory->expects($this->once())
        ->method('create')
        ->willReturn($this->createMock(SelectBuilderInterface::class));
    
    $dml = new DmlManager(/* ... */ $selectFactory /* ... */);
    $dml->select('*');
}
```

## Custom Factory Implementations

### Creating a Custom Factory

```php
namespace App\Database\Factory;

use Concept\DBAL\DML\Builder\Factory\SelectBuilderFactoryInterface;
use Concept\DBAL\DML\Builder\SelectBuilderInterface;

class CachedSelectBuilderFactory implements SelectBuilderFactoryInterface
{
    private array $cache = [];

    public function create(array $args = []): SelectBuilderInterface
    {
        $key = serialize($args);
        
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = new CustomSelectBuilder(...$args);
        }
        
        return clone $this->cache[$key];
    }
}
```

### Registering Custom Factory

Update `concept.json`:

```json
{
    "singularity": {
        "package": {
            "concept-labs/dbal": {
                "preference": {
                    "Concept\\DBAL\\DML\\Builder\\Factory\\SelectBuilderFactoryInterface": {
                        "class": "App\\Database\\Factory\\CachedSelectBuilderFactory"
                    }
                }
            }
        }
    }
}
```

## Factory Hierarchy

```
ServiceFactory (from Singularity)
    ├── DmlManagerFactory
    ├── DdlManagerFactory
    ├── SelectBuilderFactory
    ├── InsertBuilderFactory
    ├── UpdateBuilderFactory
    ├── DeleteBuilderFactory
    ├── CreateTableBuilderFactory
    ├── AlterTableBuilderFactory
    ├── DropTableBuilderFactory
    └── TruncateTableBuilderFactory
```

## Best Practices

### 1. Always Use Interfaces

```php
// Good
public function __construct(
    private SelectBuilderFactoryInterface $selectFactory
) {}

// Bad
public function __construct(
    private SelectBuilderFactory $selectFactory
) {}
```

### 2. Clone Builders

```php
// Good - each call gets fresh builder
return clone $this->selectBuilderPrototype;

// Bad - shared state between calls
return $this->selectBuilderPrototype;
```

### 3. Lazy Initialization

```php
// Good - create only when needed
if (null === $this->prototype) {
    $this->prototype = $this->factory->create();
}

// Bad - create in constructor (may not be used)
public function __construct() {
    $this->prototype = $this->factory->create();
}
```

### 4. Type Hints

```php
// Good - explicit return types
public function create(array $args = []): SelectBuilderInterface

// Bad - no type hints
public function create($args = [])
```

## Troubleshooting

### Factory Not Found

```php
// Error: Factory interface not registered
$factory = $container->get(SelectBuilderFactoryInterface::class);
```

**Solution**: Register in `concept.json`:

```json
{
    "preference": {
        "Concept\\DBAL\\DML\\Builder\\Factory\\SelectBuilderFactoryInterface": {
            "class": "Concept\\DBAL\\DML\\Builder\\Factory\\SelectBuilderFactory"
        }
    }
}
```

### Wrong Builder Type

```php
// Error: Type mismatch
$builder = $this->factory->create(); // Returns wrong type
```

**Solution**: Check factory implementation returns correct interface.

### Circular Dependencies

```php
// Error: Circular dependency detected
class FactoryA depends on FactoryB
class FactoryB depends on FactoryA
```

**Solution**: Restructure dependencies or use lazy loading.

## Summary

The Factory Pattern in DBAL provides:

✅ **Loose Coupling**: Depend on interfaces, not implementations  
✅ **Flexibility**: Easy to swap implementations  
✅ **Testability**: Simple mocking and testing  
✅ **Consistency**: Standardized object creation  
✅ **Performance**: Prototype pattern optimization  
✅ **Maintainability**: Clear separation of concerns
