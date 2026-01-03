# Framework Integration Guide

This guide shows how to integrate Concept DBAL with popular modern PHP frameworks.

## Overview

While Concept DBAL is designed for the Concept Ecosystem with Singularity DI, it can be integrated into any modern PHP framework that supports dependency injection.

The key integration point is **`Concept\DBAL\DbalManager`** - the primary injectable service.

## Laravel Integration

### Installation

```bash
composer require concept-labs/dbal
```

### Service Provider

Create a service provider to register DBAL services:

```php
<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Concept\DBAL\DbalManager;
use Concept\DBAL\DbalManagerInterface;
use Concept\DBC\Pdo\PdoConnection;
use Concept\DBAL\DML\DmlManager;
use Concept\DBAL\DML\DmlManagerInterface;
use Concept\DBAL\DML\Expression\SqlExpressionFactory;
use Concept\DBAL\DML\Builder\Factory\SelectBuilderFactory;
use Concept\DBAL\DML\Builder\Factory\InsertBuilderFactory;
use Concept\DBAL\DML\Builder\Factory\UpdateBuilderFactory;
use Concept\DBAL\DML\Builder\Factory\DeleteBuilderFactory;
use Concept\DBAL\DML\Builder\Factory\RawBuilderFactory;

class DbalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register DbalManager as singleton
        $this->app->singleton(DbalManagerInterface::class, function ($app) {
            // Get Laravel's DB connection config
            $config = config('database.connections.mysql');
            
            // Create PDO connection
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                $config['host'],
                $config['database']
            );
            
            $connection = new PdoConnection(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]
            );
            
            // Create expression factory
            $exprFactory = new SqlExpressionFactory();
            $sqlExpression = $exprFactory->create();
            $sqlExpression->setQuoteDecorator(fn($v) => $connection->quote($v));
            
            // Create builder factories
            $rawFactory = new RawBuilderFactory($sqlExpression);
            $selectFactory = new SelectBuilderFactory($sqlExpression);
            $insertFactory = new InsertBuilderFactory($sqlExpression);
            $updateFactory = new UpdateBuilderFactory($sqlExpression);
            $deleteFactory = new DeleteBuilderFactory($sqlExpression);
            
            // Create DML manager
            $dml = new DmlManager(
                $sqlExpression,
                $rawFactory,
                $selectFactory,
                $insertFactory,
                $updateFactory,
                $deleteFactory
            );
            
            // Create and return DBAL manager
            return new DbalManager($connection, $dialect, $dml, null);
        });
        
        // Also bind DmlManagerInterface for direct access
        $this->app->singleton(DmlManagerInterface::class, function ($app) {
            return $app->make(DbalManagerInterface::class)->dml();
        });
    }
}
```

### Register Provider

Add to `config/app.php`:

```php
'providers' => [
    // ...
    App\Providers\DbalServiceProvider::class,
],
```

### Usage in Laravel

```php
<?php
namespace App\Repositories;

use Concept\DBAL\DbalManagerInterface;

class UserRepository
{
    public function __construct(
        private DbalManagerInterface $dbal
    ) {}
    
    public function findActive(): array
    {
        return $this->dbal->dml()
            ->select('id', 'name', 'email')
            ->from('users')
            ->where($this->dbal->dml()->expr()->condition('status', '=', 'active'))
            ->execute();
    }
}

// In controller:
class UserController extends Controller
{
    public function index(UserRepository $repository)
    {
        $users = $repository->findActive();
        return view('users.index', compact('users'));
    }
}
```

### Laravel Facade (Optional)

Create a facade for easier access:

```php
<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use Concept\DBAL\DbalManagerInterface;

class Dbal extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DbalManagerInterface::class;
    }
}

// Usage:
use App\Facades\Dbal;

$users = Dbal::dml()->select('*')->from('users')->execute();
```

## Symfony Integration

### Installation

```bash
composer require concept-labs/dbal
```

### Service Configuration

Create `config/services/dbal.yaml`:

```yaml
services:
    # Connection
    Concept\DBC\ConnectionInterface:
        class: Concept\DBC\Pdo\PdoConnection
        arguments:
            $dsn: '%env(DATABASE_URL)%'
            $username: '%env(DATABASE_USER)%'
            $password: '%env(DATABASE_PASSWORD)%'
    
    # Expression Factory
    Concept\DBAL\DML\Expression\SqlExpressionFactory:
        class: Concept\DBAL\DML\Expression\SqlExpressionFactory
    
    # SQL Expression
    Concept\DBAL\DML\Expression\SqlExpressionInterface:
        factory: ['@Concept\DBAL\DML\Expression\SqlExpressionFactory', 'create']
    
    # Builder Factories
    Concept\DBAL\DML\Builder\Factory\RawBuilderFactory:
        arguments:
            $sqlExpression: '@Concept\DBAL\DML\Expression\SqlExpressionInterface'
    
    Concept\DBAL\DML\Builder\Factory\SelectBuilderFactory:
        arguments:
            $sqlExpression: '@Concept\DBAL\DML\Expression\SqlExpressionInterface'
    
    Concept\DBAL\DML\Builder\Factory\InsertBuilderFactory:
        arguments:
            $sqlExpression: '@Concept\DBAL\DML\Expression\SqlExpressionInterface'
    
    Concept\DBAL\DML\Builder\Factory\UpdateBuilderFactory:
        arguments:
            $sqlExpression: '@Concept\DBAL\DML\Expression\SqlExpressionInterface'
    
    Concept\DBAL\DML\Builder\Factory\DeleteBuilderFactory:
        arguments:
            $sqlExpression: '@Concept\DBAL\DML\Expression\SqlExpressionInterface'
    
    # DML Manager
    Concept\DBAL\DML\DmlManagerInterface:
        class: Concept\DBAL\DML\DmlManager
        arguments:
            $sqlExpressionPrototype: '@Concept\DBAL\DML\Expression\SqlExpressionInterface'
            $rawBuilderFactory: '@Concept\DBAL\DML\Builder\Factory\RawBuilderFactory'
            $selectBuilderFactory: '@Concept\DBAL\DML\Builder\Factory\SelectBuilderFactory'
            $insertBuilderFactory: '@Concept\DBAL\DML\Builder\Factory\InsertBuilderFactory'
            $updateBuilderFactory: '@Concept\DBAL\DML\Builder\Factory\UpdateBuilderFactory'
            $deleteBuilderFactory: '@Concept\DBAL\DML\Builder\Factory\DeleteBuilderFactory'
    
    # DBAL Manager (Primary Service)
    Concept\DBAL\DbalManagerInterface:
        class: Concept\DBAL\DbalManager
        arguments:
            $connection: '@Concept\DBC\ConnectionInterface'
            $dialect: '@Concept\DBAL\DialectInterface'
            $dml: '@Concept\DBAL\DML\DmlManagerInterface'
            $ddl: null
```

### Usage in Symfony

```php
<?php
namespace App\Repository;

use Concept\DBAL\DbalManagerInterface;

class UserRepository
{
    public function __construct(
        private DbalManagerInterface $dbal
    ) {}
    
    public function findAll(): array
    {
        return $this->dbal->dml()
            ->select('*')
            ->from('users')
            ->execute();
    }
}

// In controller:
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\UserRepository;

class UserController extends AbstractController
{
    public function index(UserRepository $repository)
    {
        $users = $repository->findAll();
        return $this->json($users);
    }
}
```

## Slim Framework Integration

### Installation

```bash
composer require concept-labs/dbal
composer require php-di/php-di
```

### Container Configuration

```php
<?php
use DI\ContainerBuilder;
use Concept\DBAL\DbalManager;
use Concept\DBAL\DbalManagerInterface;
use Concept\DBC\Pdo\PdoConnection;
use Concept\DBAL\DML\DmlManager;
use Concept\DBAL\DML\Expression\SqlExpressionFactory;

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions([
    // Connection
    PdoConnection::class => function () {
        return new PdoConnection(
            getenv('DB_DSN'),
            getenv('DB_USER'),
            getenv('DB_PASS')
        );
    },
    
    // DBAL Manager (Primary Service)
    DbalManagerInterface::class => function ($container) {
        $connection = $container->get(PdoConnection::class);
        
        // Setup expression and factories
        $exprFactory = new SqlExpressionFactory();
        $sqlExpression = $exprFactory->create();
        $sqlExpression->setQuoteDecorator(fn($v) => $connection->quote($v));
        
        // Create factories
        $rawFactory = new RawBuilderFactory($sqlExpression);
        $selectFactory = new SelectBuilderFactory($sqlExpression);
        $insertFactory = new InsertBuilderFactory($sqlExpression);
        $updateFactory = new UpdateBuilderFactory($sqlExpression);
        $deleteFactory = new DeleteBuilderFactory($sqlExpression);
        
        // Create DML
        $dml = new DmlManager(
            $sqlExpression,
            $rawFactory,
            $selectFactory,
            $insertFactory,
            $updateFactory,
            $deleteFactory
        );
        
        return new DbalManager($connection, $dialect, $dml, null);
    },
]);

$container = $containerBuilder->build();

// Create Slim app
$app = \Slim\Factory\AppFactory::createFromContainer($container);
```

### Usage in Slim

```php
<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Concept\DBAL\DbalManagerInterface;

$app->get('/users', function (Request $request, Response $response) {
    $dbal = $this->get(DbalManagerInterface::class);
    
    $users = $dbal->dml()
        ->select('*')
        ->from('users')
        ->execute();
    
    $response->getBody()->write(json_encode($users));
    return $response->withHeader('Content-Type', 'application/json');
});
```

## Laminas/Mezzio Integration

### Service Factory

```php
<?php
namespace App\Factory;

use Psr\Container\ContainerInterface;
use Concept\DBAL\DbalManager;
use Concept\DBAL\DbalManagerInterface;

class DbalManagerFactory
{
    public function __invoke(ContainerInterface $container): DbalManagerInterface
    {
        $config = $container->get('config')['database'];
        
        $connection = new PdoConnection(
            $config['dsn'],
            $config['username'],
            $config['password']
        );
        
        // Setup expression and builders...
        
        return new DbalManager($connection, $dialect, $dml, null);
    }
}
```

### Configuration

```php
// config/autoload/dependencies.global.php
return [
    'dependencies' => [
        'factories' => [
            DbalManagerInterface::class => App\Factory\DbalManagerFactory::class,
        ],
    ],
];
```

## CodeIgniter 4 Integration

### Service Registration

```php
<?php
// app/Config/Services.php
namespace Config;

use CodeIgniter\Config\BaseService;
use Concept\DBAL\DbalManager;
use Concept\DBAL\DbalManagerInterface;

class Services extends BaseService
{
    public static function dbal($getShared = true): DbalManagerInterface
    {
        if ($getShared) {
            return static::getSharedInstance('dbal');
        }
        
        $config = config('Database');
        
        $connection = new \Concept\DBC\Pdo\PdoConnection(
            $config->default['DSN'],
            $config->default['username'],
            $config->default['password']
        );
        
        // Setup DBAL...
        
        return new DbalManager($connection, $dialect, $dml, null);
    }
}
```

### Usage

```php
<?php
namespace App\Controllers;

class Users extends BaseController
{
    public function index()
    {
        $dbal = service('dbal');
        
        $users = $dbal->dml()
            ->select('*')
            ->from('users')
            ->execute();
        
        return $this->response->setJSON($users);
    }
}
```

## Standalone with PSR-11 Container

For any PSR-11 compatible container:

```php
<?php
use Psr\Container\ContainerInterface;

class DbalFactory
{
    public function __invoke(ContainerInterface $container)
    {
        // Get dependencies from container
        $connection = $container->get(ConnectionInterface::class);
        
        // Create DBAL
        // ...
        
        return new DbalManager($connection, $dialect, $dml, null);
    }
}

// Register in container
$container->set(DbalManagerInterface::class, new DbalFactory());
```

## Best Practices for Framework Integration

### 1. Use Interface Type Hints

```php
// Good
public function __construct(private DbalManagerInterface $dbal) {}

// Bad
public function __construct(private DbalManager $dbal) {}
```

### 2. Register as Singleton

Most DI containers should register DBAL as a singleton/shared service.

### 3. Use Environment Configuration

```php
// .env
DB_DSN=mysql:host=localhost;dbname=myapp
DB_USER=root
DB_PASS=secret

// Use in service registration
$dsn = getenv('DB_DSN') ?: config('database.dsn');
```

### 4. Create Repository Layer

```php
class UserRepository
{
    public function __construct(
        private DbalManagerInterface $dbal
    ) {}
    
    // Repository methods...
}
```

### 5. Use Framework's Connection Config

Leverage existing database configuration:

```php
// Laravel
$config = config('database.connections.mysql');

// Symfony
$config = $container->getParameter('database_url');

// CodeIgniter
$config = config('Database')->default;
```

## Testing with Frameworks

### Laravel Testing

```php
class UserRepositoryTest extends TestCase
{
    public function test_find_active_users()
    {
        $dbal = $this->app->make(DbalManagerInterface::class);
        $repository = new UserRepository($dbal);
        
        $users = $repository->findActive();
        
        $this->assertIsArray($users);
    }
}
```

### Symfony Testing

```php
class UserRepositoryTest extends KernelTestCase
{
    public function testFindAll(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $dbal = $container->get(DbalManagerInterface::class);
        $repository = new UserRepository($dbal);
        
        $users = $repository->findAll();
        
        $this->assertIsArray($users);
    }
}
```

## Next Steps

- **[Standalone Usage](standalone-usage.md)** - Use without a framework
- **[Dependency Injection](dependency-injection.md)** - Use with Singularity
- **[Best Practices](best-practices.md)** - Framework integration patterns
- **[Examples](examples.md)** - Real-world usage examples
