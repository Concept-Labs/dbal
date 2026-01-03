# Architecture Guide

This guide provides a deep dive into the architecture and design patterns used in Concept DBAL.

## Architectural Overview

Concept DBAL is built on several key architectural principles:

1. **Interface-Driven Design** - Every component has a well-defined interface
2. **Dependency Injection** - All dependencies are injected via constructors
3. **Factory Pattern** - Builders are created through factories
4. **Prototype Pattern** - Efficient object cloning for query builders
5. **Fluent Interface** - Chainable method calls for intuitive API
6. **Separation of Concerns** - Clear boundaries between components

## Component Architecture

```
┌─────────────────────────────────────────────────────┐
│                  DbalManager                         │
│  (Main entry point for database operations)          │
└────────────────┬────────────────────────────────────┘
                 │
                 ├─────────────────┬──────────────────┐
                 │                 │                  │
         ┌───────▼──────┐  ┌──────▼─────┐   ┌───────▼──────┐
         │ DmlManager   │  │ DdlManager │   │ Connection   │
         │ (DML Ops)    │  │ (DDL Ops)  │   │   (PDO)      │
         └───────┬──────┘  └────────────┘   └──────────────┘
                 │
                 │
    ┌────────────┼────────────────────────┐
    │            │                        │
┌───▼───┐  ┌────▼─────┐  ┌───────▼──────┐  ┌────────────┐
│Select │  │  Insert  │  │   Update     │  │   Delete   │
│Builder│  │ Builder  │  │   Builder    │  │   Builder  │
└───┬───┘  └────┬─────┘  └───────┬──────┘  └─────┬──────┘
    │           │                 │                │
    └───────────┴─────────────────┴────────────────┘
                         │
                 ┌───────▼──────────┐
                 │  SqlExpression   │
                 │  (Query Parts)   │
                 └──────────────────┘
```

## Core Components

### 1. DbalManager

The top-level manager coordinating all database operations.

```php
interface DbalManagerInterface
{
    public function dml(): DmlManagerInterface;
    public function ddl(): DdlManagerInterface;
}
```

**Responsibilities:**
- Provide access to DML (Data Manipulation Language) operations
- Provide access to DDL (Data Definition Language) operations
- Manage database connection
- Handle database dialect differences

**Design Pattern:** Facade Pattern - Simplifies access to complex subsystems.

### 2. DmlManager

Manages data manipulation operations (SELECT, INSERT, UPDATE, DELETE).

```php
interface DmlManagerInterface extends SqlExpressionAwareInterface
{
    public function select(...$columns): SelectBuilderInterface;
    public function insert(?string $table = null): InsertBuilderInterface;
    public function update(string|array $table): UpdateBuilderInterface;
    public function delete(?string $table = null): DeleteBuilderInterface;
}
```

**Responsibilities:**
- Create query builder instances
- Manage builder factories
- Provide SQL expression utilities
- Cache builder prototypes for performance

**Design Pattern:** Factory Method Pattern - Delegates builder creation to factories.

**Prototype Pattern Implementation:**
```php
class DmlManager implements DmlManagerInterface
{
    private ?SelectBuilderInterface $selectBuilderPrototype = null;
    
    protected function getSelectBuilder(): SelectBuilderInterface
    {
        // Create prototype on first call
        if (null === $this->selectBuilderPrototype) {
            $this->selectBuilderPrototype = $this->selectBuilderFactory->create();
        }
        
        // Clone for each new query
        return clone $this->selectBuilderPrototype;
    }
}
```

**Why Prototype Pattern?**
- Avoids repeated initialization overhead
- Builders can be expensive to create (many dependencies)
- Cloning is faster than constructing new instances
- Each query gets a fresh, independent builder

### 3. Query Builders

Each query type has a dedicated builder implementing a fluent interface.

#### SelectBuilder

```php
interface SelectBuilderInterface extends 
    SqlBuilderInterface,
    SelectableInterface,
    ConditionableInterface,
    JoinableInterface,
    GroupableInterface,
    OrderableInterface,
    LimitableInterface
{
    public function select(...$columns): static;
    public function from(string|array $table): static;
    public function where(SqlExpressionInterface $condition): static;
    public function join(string $table, SqlExpressionInterface $condition): static;
    public function groupBy(...$columns): static;
    public function orderBy(string $column, string $direction): static;
    public function limit(int $limit): static;
}
```

**Key Features:**
- **Trait Composition** - Functionality divided into reusable traits
- **Type Safety** - Strong typing on all parameters
- **Fluent API** - All methods return `static` for chaining
- **Immutable Sections** - Query sections can be reset independently

#### InsertBuilder

```php
interface InsertBuilderInterface extends SqlBuilderInterface
{
    public function insert(?string $table = null): static;
    public function into(string $table): static;
    public function values(array ...$values): static;
    public function ignore(): static;
}
```

#### UpdateBuilder

```php
interface UpdateBuilderInterface extends SqlBuilderInterface
{
    public function update(string|array $table): static;
    public function set(string|array $column, mixed $value = null): static;
    public function where(SqlExpressionInterface $condition): static;
}
```

#### DeleteBuilder

```php
interface DeleteBuilderInterface extends SqlBuilderInterface
{
    public function delete(?string $table = null): static;
    public function from(string $table): static;
    public function where(SqlExpressionInterface $condition): static;
}
```

### 4. SqlExpression System

The expression system builds SQL fragments in a type-safe manner.

```php
interface SqlExpressionInterface extends ExpressionInterface
{
    // Core methods
    public function keyword(string $keyword): static;
    public function identifier(string $identifier): static;
    public function value(string $value): static;
    public function alias(string $alias, string|SqlExpressionInterface $expression): static;
    
    // Condition methods
    public function condition(string|SqlExpressionInterface $left, string $operator, mixed $right): static;
    public function in(string|SqlExpressionInterface $column, array|SqlExpressionInterface $values): static;
    public function like(string|SqlExpressionInterface $column, string|SqlExpressionInterface $value): static;
    
    // Aggregate functions
    public function count(string $column, ?string $alias = null): static;
    public function sum(string $column, ?string $alias = null): static;
    public function avg(string $column, ?string $alias = null): static;
    public function max(string $column, ?string $alias = null): static;
    public function min(string $column, ?string $alias = null): static;
}
```

**Expression Types:**
- **Identifier** - Table/column names (escaped)
- **Value** - Literal values (quoted)
- **Keyword** - SQL keywords (SELECT, FROM, WHERE)
- **Operator** - Comparison operators (=, >, <, LIKE)
- **Condition** - Complete conditions (age > 18)
- **Alias** - Column/table aliases (name AS full_name)

### 5. Factory Pattern

Factories create instances of builders and expressions.

```php
interface SelectBuilderFactoryInterface
{
    public function create(): SelectBuilderInterface;
}

class SelectBuilderFactory implements SelectBuilderFactoryInterface
{
    public function __construct(
        private SqlExpressionInterface $sqlExpression
    ) {}
    
    public function create(): SelectBuilderInterface
    {
        return new SelectBuilder($this->sqlExpression);
    }
}
```

**Benefits:**
- **Flexibility** - Easy to swap implementations
- **Testability** - Mock factories in tests
- **Dependency Management** - Centralized object creation
- **Lifecycle Control** - Manage object lifecycle (prototype, shared, etc.)

## Design Patterns in Detail

### 1. Interface-Driven Design

Every component is defined by an interface first:

```php
// Interface defines contract
interface DmlManagerInterface {
    public function select(...$columns): SelectBuilderInterface;
}

// Implementation provides behavior
class DmlManager implements DmlManagerInterface {
    public function select(...$columns): SelectBuilderInterface {
        return $this->getSelectBuilder()->select(...$columns);
    }
}
```

**Benefits:**
- **Loose Coupling** - Depend on abstractions, not concrete classes
- **Testability** - Easy to create mocks and test doubles
- **Flexibility** - Swap implementations without changing client code
- **Documentation** - Interface serves as a contract and documentation

### 2. Dependency Injection

All dependencies are injected via constructors:

```php
class DmlManager implements DmlManagerInterface
{
    public function __construct(
        private SqlExpressionInterface $sqlExpressionPrototype,
        private RawBuilderFactoryInterface $rawBuilderFactory,
        private SelectBuilderFactoryInterface $selectBuilderFactory,
        private InsertBuilderFactoryInterface $insertBuilderFactory,
        private UpdateBuilderFactoryInterface $updateBuilderFactory,
        private DeleteBuilderFactoryInterface $deleteBuilderFactory
    ) {}
}
```

**Benefits:**
- **Testability** - Inject mocks in unit tests
- **Flexibility** - Configure dependencies externally
- **No Hidden Dependencies** - All dependencies explicit in constructor
- **Inversion of Control** - Framework manages object lifecycle

### 3. Fluent Interface

Methods return `$this` or `static` for method chaining:

```php
class SelectBuilder implements SelectBuilderInterface
{
    public function select(...$columns): static
    {
        // Implementation
        return $this;
    }
    
    public function from(string $table): static
    {
        // Implementation
        return $this;
    }
}

// Usage
$query = $builder->select('id', 'name')
    ->from('users')
    ->where($condition)
    ->orderBy('name')
    ->limit(10);
```

**Benefits:**
- **Readability** - Reads like natural language
- **Discoverability** - IDE autocomplete guides usage
- **Reduced Boilerplate** - No need for intermediate variables
- **Method Chaining** - Compose complex queries fluently

### 4. Trait Composition

Functionality divided into reusable traits:

```php
class SelectBuilder extends SqlBuilder implements SelectBuilderInterface
{
    use SelectTrait;       // SELECT clause methods
    use ConditionTrait;    // WHERE clause methods
    use JoinTrait;         // JOIN clause methods
    use FromTrait;         // FROM clause methods
    use OrderByTrait;      // ORDER BY clause methods
    use GroupTrait;        // GROUP BY clause methods
    use UnionTrait;        // UNION clause methods
    use LockTrait;         // Locking methods
    use ExplainTrait;      // EXPLAIN methods
}
```

**Benefits:**
- **Reusability** - Share functionality across builders
- **Single Responsibility** - Each trait handles one concern
- **Maintainability** - Easier to locate and modify functionality
- **Flexibility** - Mix and match traits as needed

### 5. Pipeline Pattern

Query building uses a pipeline of sections:

```php
protected function getPipeline(): SqlExpressionInterface
{
    return $this->expression(
        $this->pipeSection(KeywordEnum::SELECT),
        $this->pipeSection(KeywordEnum::FROM),
        $this->pipeSection(KeywordEnum::JOIN, false),
        $this->pipeSection(KeywordEnum::WHERE),
        $this->pipeSection(KeywordEnum::GROUP_BY),
        $this->pipeSection(KeywordEnum::HAVING),
        $this->pipeSection(KeywordEnum::ORDER_BY),
        $this->pipeSection(KeywordEnum::LIMIT)
    )->join(' ');
}
```

**Benefits:**
- **Ordered Processing** - SQL clauses in correct order
- **Flexibility** - Easy to add/remove sections
- **Composability** - Sections can be optional
- **Clarity** - Clear query structure

## Thread Safety and Immutability

### Builder Independence

Each query gets its own builder instance via cloning:

```php
$query1 = $dml->select('*')->from('users');
$query2 = $dml->select('*')->from('orders');

// query1 and query2 are independent
// Changes to query1 don't affect query2
```

### Section Reset

Query sections can be reset independently:

```php
$builder->select('*')
    ->from('users')
    ->where($condition1);

// Reset WHERE section, keep SELECT and FROM
$builder->resetSection(KeywordEnum::WHERE)
    ->where($condition2);
```

## Performance Considerations

### 1. Prototype Pattern

Builders use prototype pattern to avoid repeated initialization:
- **First call**: Create prototype (expensive)
- **Subsequent calls**: Clone prototype (cheap)

### 2. Lazy Initialization

Components initialized only when needed:

```php
private ?SelectBuilderInterface $selectBuilderPrototype = null;

protected function getSelectBuilder(): SelectBuilderInterface
{
    if (null === $this->selectBuilderPrototype) {
        $this->selectBuilderPrototype = $this->selectBuilderFactory->create();
    }
    return clone $this->selectBuilderPrototype;
}
```

### 3. Expression Pooling

Expression objects can be reused:

```php
$expr = $dml->expr();

// Reuse expression object for multiple conditions
$query->where($expr->condition('age', '>', 18))
    ->andWhere($expr->condition('status', '=', 'active'));
```

## Extension Points

### Custom Builder

Extend builders to add custom functionality:

```php
class CustomSelectBuilder extends SelectBuilder
{
    public function whereActive(): static
    {
        return $this->where(
            $this->expression()->condition('status', '=', 'active')
        );
    }
    
    public function withUserProfile(): static
    {
        return $this->leftJoin(
            'profiles',
            $this->expression()->condition('users.id', '=', 'profiles.user_id')
        );
    }
}
```

### Custom Expression

Add custom expression methods:

```php
class CustomSqlExpression extends SqlExpression
{
    public function fullTextSearch(string $column, string $query): static
    {
        return $this->push(
            $this->keyword('MATCH'),
            $this->group($this->identifier($column)),
            $this->keyword('AGAINST'),
            $this->group($this->quote($query))
        );
    }
}
```

### Custom Factory

Create custom factories for custom builders:

```php
class CustomSelectBuilderFactory implements SelectBuilderFactoryInterface
{
    public function create(): SelectBuilderInterface
    {
        return new CustomSelectBuilder($this->sqlExpression);
    }
}
```

## Next Steps

- **[Query Builders](builders.md)** - Detailed builder documentation
- **[SQL Expressions](expressions.md)** - Expression system guide
- **[Best Practices](best-practices.md)** - Recommended patterns
- **[Dependency Injection](dependency-injection.md)** - DI integration guide
