# Dependency Injection with Singularity

Concept DBAL is designed from the ground up to work seamlessly with [Singularity](https://github.com/Concept-Labs/singularity), a powerful dependency injection container for PHP.

## Why Dependency Injection?

Dependency Injection (DI) provides several key benefits:

- **Loose Coupling** - Classes depend on interfaces, not concrete implementations
- **Testability** - Easy to inject mocks and test doubles
- **Flexibility** - Configure dependencies externally
- **Maintainability** - Clear dependency relationships
- **Reusability** - Components can be easily reused in different contexts

## Singularity Container

[Singularity](https://github.com/Concept-Labs/singularity) is Concept Labs' dependency injection container, designed for modern PHP applications with features like:

- **Auto-wiring** - Automatic dependency resolution
- **Interface Bindings** - Map interfaces to implementations
- **Lifecycle Management** - Control object lifecycle (shared, prototype, etc.)
- **Configuration** - JSON-based service definitions
- **Performance** - Optimized for production use

## Installation

First, install both DBAL and Singularity:

```bash
composer require concept-labs/dbal
composer require concept-labs/singularity
```

## Configuration

### concept.json

Concept DBAL includes a `concept.json` file that defines all service bindings for Singularity:

```json
{
    "singularity": {
        "package": {
            "concept-labs/dbal": {
                "preference": {
                    "Concept\\DBAL\\DML\\DmlManagerInterface": {
                        "class": "Concept\\DBAL\\DML\\DmlManager"
                    },
                    "Concept\\DBAL\\DML\\Expression\\SqlExpressionInterface": {
                        "class": "Concept\\DBAL\\DML\\Expression\\SqlExpression"
                    },
                    "Concept\\DBAL\\DML\\Expression\\SqlExpressionFactoryInterface": {
                        "class": "Concept\\DBAL\\DML\\Expression\\SqlExpressionFactory"
                    },
                    "Concept\\DBAL\\DML\\DmlManagerFactoryInterface": {
                        "class": "Concept\\DBAL\\DML\\DmlManagerFactory"
                    },
                    "Concept\\DBAL\\DML\\Builder\\Factory\\RawBuilderFactoryInterface": {
                        "class": "Concept\\DBAL\\DML\\Builder\\Factory\\RawBuilderFactory"
                    },
                    "Concept\\DBAL\\DML\\Builder\\RawBuilderInterface": {
                        "class": "Concept\\DBAL\\DML\\Builder\\RawBuilder"
                    },
                    "Concept\\DBAL\\DML\\Builder\\Factory\\SelectBuilderFactoryInterface": {
                        "class": "Concept\\DBAL\\DML\\Builder\\Factory\\SelectBuilderFactory"
                    },
                    "Concept\\DBAL\\DML\\Builder\\SelectBuilderInterface": {
                        "class": "Concept\\DBAL\\DML\\Builder\\SelectBuilder"
                    },
                    "Concept\\DBAL\\DML\\Builder\\Factory\\InsertBuilderFactoryInterface": {
                        "class": "Concept\\DBAL\\DML\\Builder\\Factory\\InsertBuilderFactory"
                    },
                    "Concept\\DBAL\\DML\\Builder\\InsertBuilderInterface": {
                        "class": "Concept\\DBAL\\DML\\Builder\\InsertBuilder"
                    },
                    "Concept\\DBAL\\DML\\Builder\\Factory\\UpdateBuilderFactoryInterface": {
                        "class": "Concept\\DBAL\\DML\\Builder\\Factory\\UpdateBuilderFactory"
                    },
                    "Concept\\DBAL\\DML\\Builder\\UpdateBuilderInterface": {
                        "class": "Concept\\DBAL\\DML\\Builder\\UpdateBuilder"
                    },
                    "Concept\\DBAL\\DML\\Builder\\Factory\\DeleteBuilderFactoryInterface": {
                        "class": "Concept\\DBAL\\DML\\Builder\\Factory\\DeleteBuilderFactory"
                    },
                    "Concept\\DBAL\\DML\\Builder\\DeleteBuilderInterface": {
                        "class": "Concept\\DBAL\\DML\\Builder\\DeleteBuilder"
                    }
                }
            }
        }
    }
}
```

These bindings tell Singularity which concrete classes to use when an interface is requested.

## Basic Setup

### Step 1: Create Container

```php
<?php
require_once 'vendor/autoload.php';

use Concept\Singularity\Container;

$container = new Container();
```

### Step 2: Configure Database Connection

```php
use Concept\DBC\ConnectionInterface;
use Concept\DBC\Pdo\PdoConnection;

$container->bind(ConnectionInterface::class, function() {
    return new PdoConnection(
        dsn: 'mysql:host=localhost;dbname=myapp;charset=utf8mb4',
        username: 'root',
        password: 'password',
        options: [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]
    );
});
```

### Step 3: Resolve Dependencies

```php
use Concept\DBAL\DML\DmlManagerInterface;

// Container auto-wires all dependencies
$dml = $container->get(DmlManagerInterface::class);

// Use it
$users = $dml->select('*')->from('users')->execute();
```

## Lifecycle Management

### Shared (Singleton) Services

By default, DmlManager is shared (singleton) - one instance for the entire application:

```php
use Concept\Singularity\Contract\Lifecycle\SharedInterface;

class DmlManager implements DmlManagerInterface, SharedInterface
{
    // This tells Singularity to treat this as a singleton
}

// Both resolve to the same instance
$dml1 = $container->get(DmlManagerInterface::class);
$dml2 = $container->get(DmlManagerInterface::class);

var_dump($dml1 === $dml2); // true
```

**Why Shared?**
- DmlManager maintains builder prototypes for performance
- No state is shared between queries (each query gets cloned builder)
- Reduces memory and initialization overhead

### Prototype Services

Query builders are prototypes - new instance for each request:

```php
// Each select() call clones the prototype
$query1 = $dml->select('*')->from('users');
$query2 = $dml->select('*')->from('orders');

var_dump($query1 === $query2); // false - independent instances
```

## Injection into Your Classes

### Repository Pattern

Inject DmlManager into repository classes:

```php
<?php
namespace App\Repository;

use Concept\DBAL\DML\DmlManagerInterface;

class UserRepository
{
    public function __construct(
        private DmlManagerInterface $dml
    ) {}
    
    public function findAll(): array
    {
        return $this->dml->select('*')
            ->from('users')
            ->execute();
    }
    
    public function findById(int $id): ?array
    {
        $results = $this->dml->select('*')
            ->from('users')
            ->where($this->dml->expr()->condition('id', '=', $id))
            ->limit(1)
            ->execute();
            
        return $results[0] ?? null;
    }
}

// Container automatically resolves DmlManagerInterface
$userRepo = $container->get(UserRepository::class);
$users = $userRepo->findAll();
```

### Service Classes

Inject into service layer:

```php
<?php
namespace App\Service;

use App\Repository\UserRepository;
use Concept\DBAL\DML\DmlManagerInterface;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private DmlManagerInterface $dml
    ) {}
    
    public function getActiveUsers(): array
    {
        return $this->dml->select('*')
            ->from('users')
            ->where($this->dml->expr()->condition('status', '=', 'active'))
            ->execute();
    }
    
    public function deactivateUser(int $id): bool
    {
        return $this->dml->update('users')
            ->set('status', 'inactive')
            ->where($this->dml->expr()->condition('id', '=', $id))
            ->execute();
    }
}

// Container resolves all dependencies
$userService = $container->get(UserService::class);
```

### Controllers (MVC Pattern)

Inject into controllers:

```php
<?php
namespace App\Controller;

use App\Service\UserService;

class UserController
{
    public function __construct(
        private UserService $userService
    ) {}
    
    public function index(): array
    {
        return $this->userService->getActiveUsers();
    }
    
    public function show(int $id): ?array
    {
        return $this->userService->getUserById($id);
    }
}

// Container handles the entire dependency tree
$controller = $container->get(UserController::class);
```

## Advanced Configuration

### Custom Bindings

Override default bindings with custom implementations:

```php
use Concept\DBAL\DML\Builder\SelectBuilderInterface;
use App\Database\CustomSelectBuilder;

// Use custom builder instead of default
$container->bind(SelectBuilderInterface::class, CustomSelectBuilder::class);
```

### Factory Bindings

Use factories for complex object creation:

```php
$container->bind(DmlManagerInterface::class, function($container) {
    $connection = $container->get(ConnectionInterface::class);
    $sqlExpression = $container->get(SqlExpressionInterface::class);
    
    // Custom initialization logic
    $dml = new DmlManager(/* ... */);
    
    return $dml;
});
```

### Contextual Bindings

Different implementations for different contexts:

```php
// Use different connections for read and write
$container->when(ReadRepository::class)
    ->needs(ConnectionInterface::class)
    ->give(function() {
        return new PdoConnection('mysql:host=read-replica');
    });

$container->when(WriteRepository::class)
    ->needs(ConnectionInterface::class)
    ->give(function() {
        return new PdoConnection('mysql:host=master');
    });
```

## Testing with DI

### Mocking Dependencies

Easy to mock dependencies in tests:

```php
<?php
use PHPUnit\Framework\TestCase;
use Concept\DBAL\DML\DmlManagerInterface;
use App\Repository\UserRepository;

class UserRepositoryTest extends TestCase
{
    public function testFindAll()
    {
        // Create mock
        $mockDml = $this->createMock(DmlManagerInterface::class);
        $mockSelectBuilder = $this->createMock(SelectBuilderInterface::class);
        
        // Set expectations
        $mockDml->expects($this->once())
            ->method('select')
            ->with('*')
            ->willReturn($mockSelectBuilder);
            
        $mockSelectBuilder->expects($this->once())
            ->method('from')
            ->with('users')
            ->willReturnSelf();
            
        $mockSelectBuilder->expects($this->once())
            ->method('execute')
            ->willReturn([['id' => 1, 'name' => 'John']]);
        
        // Inject mock
        $repository = new UserRepository($mockDml);
        
        // Test
        $users = $repository->findAll();
        
        $this->assertCount(1, $users);
        $this->assertEquals('John', $users[0]['name']);
    }
}
```

### Test Container

Create a separate container for tests:

```php
<?php
$testContainer = new Container();

// Use in-memory database for tests
$testContainer->bind(ConnectionInterface::class, function() {
    return new PdoConnection('sqlite::memory:');
});

// Get service with test dependencies
$repository = $testContainer->get(UserRepository::class);
```

## Best Practices

### 1. Depend on Interfaces

Always type-hint interfaces, not concrete classes:

```php
// Good
public function __construct(private DmlManagerInterface $dml) {}

// Bad
public function __construct(private DmlManager $dml) {}
```

### 2. Constructor Injection

Use constructor injection, not property injection:

```php
// Good
class UserService {
    public function __construct(
        private DmlManagerInterface $dml
    ) {}
}

// Bad
class UserService {
    private DmlManagerInterface $dml;
    
    public function setDml(DmlManagerInterface $dml) {
        $this->dml = $dml;
    }
}
```

### 3. Single Responsibility

Keep classes focused on one responsibility:

```php
// Good - Focused on user data access
class UserRepository {
    public function __construct(private DmlManagerInterface $dml) {}
    
    public function findAll(): array { /* ... */ }
    public function findById(int $id): ?array { /* ... */ }
}

// Bad - Mixed concerns
class UserManager {
    public function __construct(
        private DmlManagerInterface $dml,
        private EmailService $email,
        private Logger $logger,
        private Cache $cache
    ) {}
}
```

### 4. Avoid Service Locator

Don't pass the container around:

```php
// Bad - Service locator anti-pattern
class UserService {
    public function __construct(private Container $container) {}
    
    public function doSomething() {
        $dml = $this->container->get(DmlManagerInterface::class);
    }
}

// Good - Explicit dependencies
class UserService {
    public function __construct(private DmlManagerInterface $dml) {}
    
    public function doSomething() {
        $this->dml->select('*')->from('users');
    }
}
```

## Integration with Concept Ecosystem

When using multiple Concept packages, Singularity manages all dependencies:

```php
use Concept\Config\ConfigInterface;
use Concept\DBAL\DML\DmlManagerInterface;
use Concept\DBC\ConnectionInterface;

class Application
{
    public function __construct(
        private ConfigInterface $config,        // concept-labs/config
        private DmlManagerInterface $dml,       // concept-labs/dbal
        private ConnectionInterface $connection // concept-labs/dbc-pdo
    ) {}
}

// Container resolves entire dependency graph
$app = $container->get(Application::class);
```

## Performance Tips

### 1. Use Shared Services

Mark heavy services as shared to avoid recreation:

```php
class DmlManager implements DmlManagerInterface, SharedInterface
{
    // Singularity creates only one instance
}
```

### 2. Lazy Loading

Don't inject services that might not be used:

```php
// Instead of injecting many services
public function __construct(
    private ServiceA $a,
    private ServiceB $b,
    private ServiceC $c
) {}

// Consider using factory or lazy injection
public function __construct(
    private Container $container
) {}

private function getServiceA(): ServiceA {
    return $this->container->get(ServiceA::class);
}
```

### 3. Cache Resolved Services

Let Singularity cache resolved services:

```php
// First call: Resolves dependencies
$dml1 = $container->get(DmlManagerInterface::class);

// Subsequent calls: Returns cached instance (for shared services)
$dml2 = $container->get(DmlManagerInterface::class);
```

## Troubleshooting

### Circular Dependencies

If you encounter circular dependency errors:

```php
// Problem: A depends on B, B depends on A
class ServiceA {
    public function __construct(private ServiceB $b) {}
}

class ServiceB {
    public function __construct(private ServiceA $a) {}
}

// Solution: Use setter injection or redesign
class ServiceA {
    private ServiceB $b;
    
    public function setServiceB(ServiceB $b) {
        $this->b = $b;
    }
}
```

### Missing Bindings

If a binding is not found:

```bash
# Error: "Binding not found for interface Foo"

# Solution: Register the binding
$container->bind(FooInterface::class, Foo::class);
```

## Next Steps

- **[Architecture Guide](architecture.md)** - Understand the overall architecture
- **[Best Practices](best-practices.md)** - Learn recommended patterns
- **[Examples](examples.md)** - See real-world implementations
- **[Singularity Documentation](https://github.com/Concept-Labs/singularity)** - Learn more about the DI container
