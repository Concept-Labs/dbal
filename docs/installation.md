# Installation Guide

## Requirements

Before installing Concept DBAL, ensure your system meets these requirements:

- **PHP**: 8.2 or higher
- **Composer**: Latest version recommended
- **PDO Extension**: Required for database connectivity

## Installation

### Using Composer (Recommended)

Install Concept DBAL via Composer:

```bash
composer require concept-labs/dbal
```

This will automatically install all required dependencies:
- `concept-labs/exception` - Exception handling
- `concept-labs/config` - Configuration management
- `concept-labs/expression` - Expression language base
- `concept-labs/dbc-pdo` - PDO database connection wrapper

### Verify Installation

After installation, verify that the package is installed correctly:

```bash
composer show concept-labs/dbal
```

You should see package information including version, description, and dependencies.

## Configuration

### Basic Setup without DI Container

If you're not using a dependency injection container, you can instantiate the components manually:

```php
<?php
require_once 'vendor/autoload.php';

use Concept\DBAL\DML\DmlManagerFactory;
use Concept\DBAL\DML\Expression\SqlExpressionFactory;
use Concept\DBAL\DML\Builder\Factory\SelectBuilderFactory;
use Concept\DBAL\DML\Builder\Factory\InsertBuilderFactory;
use Concept\DBAL\DML\Builder\Factory\UpdateBuilderFactory;
use Concept\DBAL\DML\Builder\Factory\DeleteBuilderFactory;
use Concept\DBAL\DML\Builder\Factory\RawBuilderFactory;

// Create expression factory
$exprFactory = new SqlExpressionFactory();
$sqlExpression = $exprFactory->create();

// Create builder factories
$rawBuilderFactory = new RawBuilderFactory($sqlExpression);
$selectBuilderFactory = new SelectBuilderFactory($sqlExpression);
$insertBuilderFactory = new InsertBuilderFactory($sqlExpression);
$updateBuilderFactory = new UpdateBuilderFactory($sqlExpression);
$deleteBuilderFactory = new DeleteBuilderFactory($sqlExpression);

// Create DML manager factory
$dmlFactory = new DmlManagerFactory(
    $sqlExpression,
    $rawBuilderFactory,
    $selectBuilderFactory,
    $insertBuilderFactory,
    $updateBuilderFactory,
    $deleteBuilderFactory
);

// Create DML manager
$dml = $dmlFactory->create();
```

### Setup with Singularity DI Container (Recommended)

Concept DBAL is designed to work seamlessly with the [Singularity DI Container](https://github.com/Concept-Labs/singularity). The package includes a `concept.json` configuration file that defines all service mappings.

#### Step 1: Install Singularity

```bash
composer require concept-labs/singularity
```

#### Step 2: Configure Singularity

The `concept.json` file in the DBAL package root contains all necessary DI configurations:

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
                    }
                    // ... more mappings
                }
            }
        }
    }
}
```

#### Step 3: Use Singularity Container

```php
<?php
require_once 'vendor/autoload.php';

use Concept\Singularity\Container;
use Concept\DBAL\DML\DmlManagerInterface;

// Create container
$container = new Container();

// Resolve DML Manager - all dependencies auto-wired
$dml = $container->get(DmlManagerInterface::class);

// Use in your application
$query = $dml->select('*')->from('users');
```

#### Step 4: Inject into Your Classes

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
        return $this->dml
            ->select('*')
            ->from('users')
            ->execute();
    }
}

// Container automatically resolves dependencies
$userRepo = $container->get(UserRepository::class);
```

## Database Connection

Concept DBAL uses `concept-labs/dbc-pdo` for database connectivity. Configure your database connection:

### PDO Configuration

```php
use Concept\DBC\Pdo\PdoConnection;

$connection = new PdoConnection(
    dsn: 'mysql:host=localhost;dbname=myapp;charset=utf8mb4',
    username: 'root',
    password: 'password',
    options: [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ]
);
```

### Using Connection with DBAL

```php
use Concept\DBAL\DbalManager;

$dbalManager = new DbalManager(
    connection: $connection,
    dialect: $dialect,
    dml: $dml,
    ddl: $ddl
);

// Access connection
$conn = $dbalManager->getConnection();
```

### Connection with Singularity

Configure the connection in Singularity:

```php
use Concept\DBC\ConnectionInterface;
use Concept\DBC\Pdo\PdoConnection;

$container->bind(ConnectionInterface::class, function() {
    return new PdoConnection(
        dsn: $_ENV['DB_DSN'],
        username: $_ENV['DB_USER'],
        password: $_ENV['DB_PASS']
    );
});
```

## Environment Configuration

Create a `.env` file for environment-specific configuration:

```env
DB_DSN=mysql:host=localhost;dbname=myapp;charset=utf8mb4
DB_USER=root
DB_PASS=secret
DB_CHARSET=utf8mb4
```

Load environment variables using a library like `vlucas/phpdotenv`:

```php
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Access via $_ENV or getenv()
$dsn = $_ENV['DB_DSN'];
```

## Autoloading

Concept DBAL uses PSR-4 autoloading. If you installed via Composer, autoloading is configured automatically:

```json
{
    "autoload": {
        "psr-4": {
            "Concept\\DBAL\\": "src/"
        }
    }
}
```

## Troubleshooting

### Class Not Found

If you get a "Class not found" error:

```bash
composer dump-autoload
```

### Version Conflicts

If you encounter dependency version conflicts:

```bash
composer update concept-labs/dbal
```

Or specify exact versions:

```bash
composer require concept-labs/dbal:^1.0
```

### PDO Extension Missing

Install the required PDO driver:

```bash
# Ubuntu/Debian
sudo apt-get install php8.2-mysql php8.2-pdo

# macOS (Homebrew)
brew install php@8.2

# Windows
# Enable in php.ini: extension=pdo_mysql
```

## Next Steps

Now that you have Concept DBAL installed:

- **[Quick Start Guide](quickstart.md)** - Build your first queries
- **[Dependency Injection Guide](dependency-injection.md)** - Deep dive into DI setup
- **[Query Builders](builders.md)** - Learn all query builder methods
- **[Examples](examples.md)** - See real-world usage patterns
