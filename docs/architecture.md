# DBAL Architecture

This document describes the architecture and design patterns used in the DBAL package.

## Overview

The DBAL (Database Abstraction Layer) package provides a fluent interface for database operations, separating DML (Data Manipulation Language) and DDL (Data Definition Language) concerns.

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                      DbalManager                             │
│  ┌──────────────────────┐  ┌─────────────────────────────┐ │
│  │   DmlManager         │  │   DdlManager                │ │
│  │                      │  │                             │ │
│  │  - select()          │  │  - createTable()            │ │
│  │  - insert()          │  │  - alterTable()             │ │
│  │  - update()          │  │  - dropTable()              │ │
│  │  - delete()          │  │  - truncateTable()          │ │
│  └──────────────────────┘  └─────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                    │                           │
                    ▼                           ▼
        ┌──────────────────────┐    ┌──────────────────────┐
        │  Builder Factories   │    │  Builder Factories   │
        │                      │    │                      │
        │  - SelectFactory     │    │  - CreateTableFactory│
        │  - InsertFactory     │    │  - AlterTableFactory │
        │  - UpdateFactory     │    │  - DropTableFactory  │
        │  - DeleteFactory     │    │  - TruncateFactory   │
        └──────────────────────┘    └──────────────────────┘
                    │                           │
                    ▼                           ▼
        ┌──────────────────────┐    ┌──────────────────────┐
        │  Query Builders      │    │  Schema Builders     │
        │                      │    │                      │
        │  - SelectBuilder     │    │  - CreateTableBuilder│
        │  - InsertBuilder     │    │  - AlterTableBuilder │
        │  - UpdateBuilder     │    │  - DropTableBuilder  │
        │  - DeleteBuilder     │    │  - TruncateBuilder   │
        └──────────────────────┘    └──────────────────────┘
                    │                           │
                    └───────────┬───────────────┘
                                ▼
                    ┌──────────────────────┐
                    │  SqlBuilder (Base)   │
                    │                      │
                    │  - buildQuery()      │
                    │  - execute()         │
                    │  - getExpression()   │
                    └──────────────────────┘
                                │
                                ▼
                    ┌──────────────────────┐
                    │  Connection          │
                    │  (DBC Package)       │
                    └──────────────────────┘
```

## Design Patterns

### 1. Factory Pattern

Every builder has a corresponding factory for creation:

```php
SelectBuilderFactoryInterface → SelectBuilderFactory → SelectBuilder
```

**Benefits:**
- Loose coupling between managers and builders
- Easy to swap implementations
- Testability through mocking

**Implementation:**
```php
class DmlManager {
    public function select(...$columns): SelectBuilderInterface {
        return $this->getSelectBuilder()->select(...$columns);
    }

    protected function getSelectBuilder(): SelectBuilderInterface {
        if (null === $this->prototype) {
            $this->prototype = $this->factory->create();
        }
        return clone $this->prototype;
    }
}
```

### 2. Prototype Pattern

Builders are created once and cloned for each use:

```php
// Create prototype once
$this->selectBuilderPrototype = $this->selectBuilderFactory->create();

// Clone for each use
return clone $this->selectBuilderPrototype;
```

**Benefits:**
- Performance: Builder created only once
- Isolation: Each query gets its own instance
- Memory efficiency: Shared dependencies

### 3. Builder Pattern

Fluent interface for constructing complex queries:

```php
$dml->select('*')
    ->from('users')
    ->where('status', '=', 'active')
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->execute();
```

**Benefits:**
- Readable and maintainable code
- Type-safe query construction
- Method chaining

### 4. Strategy Pattern

Different builders implement different query strategies:

```php
interface SqlBuilderInterface {
    public function execute(): ResultInterface;
    public function asExpression(): SqlExpressionInterface;
}

class SelectBuilder implements SqlBuilderInterface { }
class InsertBuilder implements SqlBuilderInterface { }
```

**Benefits:**
- Polymorphic query execution
- Extensible for new query types
- Clean separation of concerns

### 5. Manager Pattern

Managers coordinate builder creation and usage:

```php
class DmlManager {
    public function __construct(
        private SelectBuilderFactoryInterface $selectFactory,
        private InsertBuilderFactoryInterface $insertFactory,
        // ...
    ) {}
}
```

**Benefits:**
- Single point of access for related operations
- Dependency injection
- Consistent API

### 6. Dependency Injection

All dependencies injected through constructors:

```php
class DmlManager {
    public function __construct(
        private ConnectionInterface $connection,
        private SqlExpressionInterface $expression,
        private SelectBuilderFactoryInterface $selectFactory,
        // ...
    ) {}
}
```

**Benefits:**
- Loose coupling
- Testability
- Flexibility

## Component Layers

### 1. Manager Layer

**Purpose:** High-level API for database operations

**Components:**
- `DbalManager`: Main entry point
- `DmlManager`: Data manipulation operations
- `DdlManager`: Data definition operations

**Responsibilities:**
- Provide fluent API
- Coordinate factory usage
- Manage builder lifecycle

### 2. Builder Layer

**Purpose:** Construct SQL queries

**Components:**
- Query Builders (SELECT, INSERT, UPDATE, DELETE)
- Schema Builders (CREATE, ALTER, DROP, TRUNCATE)

**Responsibilities:**
- Build SQL statements
- Handle parameters and bindings
- Execute queries

### 3. Factory Layer

**Purpose:** Create builder instances

**Components:**
- Builder Factory Interfaces
- Builder Factory Implementations

**Responsibilities:**
- Instantiate builders
- Inject dependencies
- Provide clean creation API

### 4. Expression Layer

**Purpose:** Build SQL expressions with dialect support

**Components:**
- SqlExpression (moved from DML to higher level)
- SqlExpressionFactory
- SQL Dialects (MySQL, PostgreSQL, SQLite)
- Expression Functions (aggregate, string, date, etc.)

**Responsibilities:**
- Type-safe expression building
- SQL function support
- Complex expression composition
- **Multi-dialect support**
- Proper quoting and escaping

### 5. Connection Layer

**Purpose:** Database connectivity (from DBC package)

**Components:**
- ConnectionInterface
- Result handling

**Responsibilities:**
- Execute SQL
- Handle transactions
- Manage connections

## Data Flow

### DML Query Execution

1. **Client Code**
   ```php
   $result = $dbalManager->dml()->select('*')->from('users')->execute();
   ```

2. **DML Manager**
   - Receives select() call
   - Gets SelectBuilder from factory (or clones prototype)
   - Returns builder to client

3. **Select Builder**
   - Chains method calls (from, where, etc.)
   - Builds SQL query
   - Executes via connection

4. **Connection**
   - Prepares statement
   - Binds parameters
   - Executes query
   - Returns result

### DDL Schema Modification

1. **Client Code**
   ```php
   $dbalManager->ddl()->createTable('users')->addColumn(...)->execute();
   ```

2. **DDL Manager**
   - Receives createTable() call
   - Gets CreateTableBuilder from factory
   - Returns builder to client

3. **Create Table Builder**
   - Chains method calls (addColumn, primaryKey, etc.)
   - Builds CREATE TABLE SQL
   - Executes via connection

4. **Connection**
   - Executes DDL statement
   - Returns result

## Extension Points

### Custom Builders

Create custom builders by:

1. Implementing builder interface
2. Creating factory
3. Registering in concept.json

```php
class CustomSelectBuilder implements SelectBuilderInterface {
    // Custom implementation
}

class CustomSelectBuilderFactory implements SelectBuilderFactoryInterface {
    public function create(): SelectBuilderInterface {
        return new CustomSelectBuilder(/* ... */);
    }
}
```

### Custom Expressions

Extend expression system:

```php
class CustomSqlExpression extends SqlExpression {
    public function customFunction($arg) {
        return $this->function('CUSTOM_FUNC', $arg);
    }
}
```

### Custom Managers

Create domain-specific managers:

```php
class UserManager {
    public function __construct(private DmlManagerInterface $dml) {}

    public function findActiveUsers() {
        return $this->dml->select('*')
            ->from('users')
            ->where('status', '=', 'active')
            ->execute();
    }
}
```

## Configuration

### Dependency Injection Container

Configuration via `concept.json`:

```json
{
    "singularity": {
        "package": {
            "concept-labs/dbal": {
                "preference": {
                    "Interface": {
                        "class": "Implementation"
                    }
                }
            }
        }
    }
}
```

### Service Registration

All services automatically registered via Singularity container:

1. Interfaces mapped to implementations
2. Dependencies auto-resolved
3. Shared instances for managers
4. New instances for builders (via clone)

## Performance Considerations

### 1. Prototype Pattern

- Builders created once, cloned many times
- Reduces object creation overhead
- Shares immutable dependencies

### 2. Lazy Initialization

```php
if (null === $this->prototype) {
    $this->prototype = $this->factory->create();
}
```

- Builders created only when needed
- Reduces startup overhead

### 3. Connection Pooling

- Reuse database connections
- Managed by DBC package
- Reduces connection overhead

### 4. Prepared Statements

- All queries use prepared statements
- Statement caching
- Protection against SQL injection

## Security

### SQL Injection Prevention

1. **Parameter Binding**: All values bound as parameters
2. **Type Safety**: PHP 8.2+ type hints
3. **Expression System**: Safe value handling

### Input Validation

- Type checking at interface level
- Expression validation
- Connection-level sanitization

## Testing Strategy

### Unit Tests

- Mock all dependencies
- Test each component in isolation
- Use factory mocks

### Integration Tests

- Test component interactions
- Use test database
- Verify end-to-end flows

### Test Coverage

- Minimum 80% code coverage
- 100% for critical paths
- Both PHPUnit and Pest support

## Best Practices

### 1. Use Interfaces

```php
// Good
private DmlManagerInterface $dml;

// Bad
private DmlManager $dml;
```

### 2. Clone Builders

```php
// Good
return clone $this->prototype;

// Bad
return $this->prototype;
```

### 3. Inject Dependencies

```php
// Good
public function __construct(private FactoryInterface $factory) {}

// Bad
public function __construct() {
    $this->factory = new Factory();
}
```

### 4. Use Type Hints

```php
// Good
public function create(array $args = []): SelectBuilderInterface

// Bad
public function create($args = [])
```

## Future Enhancements

Potential areas for expansion:

1. **Query Caching**: Cache compiled queries
2. **Database Migrations**: Built-in migration support
3. **Schema Introspection**: Read database schema
4. **Query Logging**: Debug query execution
5. **Connection Pooling**: Advanced connection management
6. **Read/Write Splitting**: Master-slave support
7. **Sharding Support**: Horizontal partitioning
8. **ORM Layer**: Object-relational mapping

## Conclusion

The DBAL architecture emphasizes:

- **Separation of Concerns**: DML/DDL split
- **Loose Coupling**: Interface-based design
- **Extensibility**: Easy to add new features
- **Testability**: Mock-friendly architecture
- **Performance**: Efficient patterns
- **Type Safety**: PHP 8.2+ features
- **Developer Experience**: Fluent API

This architecture provides a solid foundation for database operations while remaining flexible and maintainable.
