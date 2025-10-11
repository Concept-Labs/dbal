# Concept-Labs DBAL (Database Abstraction Layer)

A modern PHP 8.2+ Database Abstraction Layer providing a fluent interface for both DML (Data Manipulation Language) and DDL (Data Definition Language) operations.

## Features

- **DML Operations**: SELECT, INSERT, UPDATE, DELETE queries with a fluent builder pattern
- **DDL Operations**: CREATE TABLE, ALTER TABLE, DROP TABLE, TRUNCATE TABLE support
- **Expression-Based**: Powerful SQL expression builder
- **Factory Pattern**: Clean dependency injection using factories
- **PSR-4 Autoloading**: Modern PHP namespace structure
- **Type Safety**: Full PHP 8.2+ type hints and strict types

## Installation

```bash
composer require concept-labs/dbal
```

## Requirements

- PHP >= 8.2
- concept-labs/exception ^1
- concept-labs/config ^2
- concept-labs/expression ^1
- concept-labs/dbc-pdo ^1

## Quick Start

### DML Operations

```php
use Concept\DBAL\DbalManager;

// Get DML manager instance
$dml = $dbalManager->dml();

// SELECT query
$result = $dml->select('id', 'name', 'email')
    ->from('users')
    ->where('status', '=', 'active')
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->execute();

// INSERT query
$dml->insert('users')
    ->values([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'status' => 'active'
    ])
    ->execute();

// UPDATE query
$dml->update('users')
    ->set('status', 'inactive')
    ->where('last_login', '<', '2023-01-01')
    ->execute();

// DELETE query
$dml->delete('users')
    ->where('status', '=', 'deleted')
    ->execute();
```

### DDL Operations

```php
// Get DDL manager instance
$ddl = $dbalManager->ddl();

// CREATE TABLE
$ddl->createTable('users')
    ->ifNotExists()
    ->addColumn('id', 'INT', ['AUTO_INCREMENT'])
    ->addColumn('name', 'VARCHAR(255)', ['NOT NULL'])
    ->addColumn('email', 'VARCHAR(255)', ['NOT NULL'])
    ->addColumn('created_at', 'TIMESTAMP', ['DEFAULT CURRENT_TIMESTAMP'])
    ->primaryKey('id')
    ->unique('email')
    ->index(['name', 'email'])
    ->options(['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4'])
    ->execute();

// ALTER TABLE
$ddl->alterTable('users')
    ->addColumn('phone', 'VARCHAR(20)')
    ->modifyColumn('name', 'VARCHAR(512)', ['NOT NULL'])
    ->dropColumn('old_field')
    ->execute();

// DROP TABLE
$ddl->dropTable('temp_users')
    ->ifExists()
    ->execute();

// TRUNCATE TABLE
$ddl->truncateTable('logs')
    ->execute();
```

## Architecture

### DML (Data Manipulation Language)

The DML layer provides builders for:
- **SelectBuilder**: Complex SELECT queries with joins, unions, subqueries
- **InsertBuilder**: INSERT and REPLACE operations
- **UpdateBuilder**: UPDATE queries with conditions
- **DeleteBuilder**: DELETE queries with conditions

### DDL (Data Definition Language)

The DDL layer provides builders for:
- **CreateTableBuilder**: CREATE TABLE with columns, constraints, and indexes
- **AlterTableBuilder**: ALTER TABLE for schema modifications
- **DropTableBuilder**: DROP TABLE operations
- **TruncateTableBuilder**: TRUNCATE TABLE operations

### Builder Pattern

All builders follow a consistent pattern:

1. **Interface**: Defines the contract (e.g., `SelectBuilderInterface`)
2. **Implementation**: Concrete builder class (e.g., `SelectBuilder`)
3. **Factory Interface**: Creates builder instances (e.g., `SelectBuilderFactoryInterface`)
4. **Factory Implementation**: Factory using dependency injection (e.g., `SelectBuilderFactory`)

### Manager Pattern

- **DmlManager**: Manages DML operations and builder creation
- **DdlManager**: Manages DDL operations and builder creation
- **DbalManager**: Main entry point providing access to both DML and DDL

## Advanced Features

### SQL Expressions

Build complex expressions:

```php
$expr = $dml->expr()
    ->field('price')
    ->multiply(1.1)
    ->greaterThan(100);
```

### Fluent Interface

All builders support method chaining:

```php
$dml->select('*')
    ->from('orders')
    ->join('users', 'orders.user_id', '=', 'users.id')
    ->where('orders.status', '=', 'completed')
    ->groupBy('users.id')
    ->having('COUNT(*)', '>', 5)
    ->orderBy('total', 'DESC')
    ->limit(20)
    ->execute();
```

### Common Table Expressions (CTE)

Support for WITH clauses:

```php
$dml->select('*')
    ->with('active_users', $subquery)
    ->from('active_users')
    ->execute();
```

## Configuration

The package uses the Singularity dependency injection container. Configuration is defined in `concept.json`:

```json
{
    "singularity": {
        "package": {
            "concept-labs/dbal": {
                "preference": {
                    "Concept\\DBAL\\DML\\DmlManagerInterface": {
                        "class": "Concept\\DBAL\\DML\\DmlManager"
                    },
                    "Concept\\DBAL\\DDL\\DdlManagerInterface": {
                        "class": "Concept\\DBAL\\DDL\\DdlManager"
                    }
                }
            }
        }
    }
}
```

## Testing

Run tests with PHPUnit:

```bash
composer test
```

Or with Pest:

```bash
composer test:pest
```

## Documentation

For detailed documentation, see the [docs](docs/) directory:

- [DML Guide](docs/dml-guide.md)
- [DDL Guide](docs/ddl-guide.md)
- [Expression Guide](docs/expression-guide.md)
- [Factory Pattern](docs/factory-pattern.md)

## Contributing

Contributions are welcome! Please follow the existing code style and patterns.

## License

Apache License 2.0. See [LICENSE](LICENSE) file for details.

## Credits

Created by Viktor Halytskyi <concept.galitsky@gmail.com>

Developed by Concept Labs

