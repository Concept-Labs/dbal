# SQL Expressions Guide

SQL Expressions provide the building blocks for constructing SQL query components in a type-safe, fluent manner.

## Overview

The `SqlExpression` system allows you to build SQL fragments programmatically instead of writing raw SQL strings. This provides:

- **Type Safety** - Compile-time checking of expression components
- **SQL Injection Prevention** - Automatic parameter binding and escaping
- **Reusability** - Create and reuse expression components
- **Composability** - Combine expressions to build complex conditions
- **Database Agnostic** - Abstract away database-specific syntax

## Accessing Expressions

Get an expression instance from the DML manager:

```php
$expr = $dml->expr();

// Use expression
$condition = $expr->condition('age', '>', 18);
```

## Expression Types

### Identifiers

Identifiers represent table or column names, properly escaped:

```php
// Column identifier
$expr->identifier('users.id');

// Table identifier
$expr->identifier('users');

// Escaped identifier (backticks for MySQL)
// Outputs: `users`.`id`
```

### Values

Values represent literal data, properly quoted and escaped:

```php
// String value
$expr->value('John Doe');

// Number value
$expr->value('42');

// Quote directly
$expr->quote('some value');
```

### Keywords

SQL keywords like SELECT, FROM, WHERE:

```php
$expr->keyword('SELECT');
$expr->keyword('FROM');
$expr->keyword('WHERE');
```

### Operators

Comparison and logical operators:

```php
// Comparison operators: =, !=, <, >, <=, >=
$expr->condition('age', '>', 18);
$expr->condition('status', '=', 'active');

// Logical operators: AND, OR
$expr->group($condition1, 'AND', $condition2);
```

## Building Conditions

### Simple Conditions

```php
// age > 18
$expr->condition('age', '>', 18);

// status = 'active'
$expr->condition('status', '=', 'active');

// email != NULL
$expr->condition('email', '!=', null);

// price <= 99.99
$expr->condition('price', '<=', 99.99);
```

### IN Conditions

```php
// status IN ('active', 'pending', 'approved')
$expr->in('status', ['active', 'pending', 'approved']);

// id IN (1, 2, 3, 4, 5)
$expr->in('id', [1, 2, 3, 4, 5]);
```

### LIKE Conditions

```php
// name LIKE 'John%'
$expr->like('name', 'John%');

// email LIKE '%@example.com'
$expr->like('email', '%@example.com');

// description LIKE '%keyword%'
$expr->like('description', '%keyword%');
```

### CASE Expressions

```php
// CASE WHEN age > 18 THEN 'adult' ELSE 'minor' END
$expr->case(
    $expr->condition('age', '>', 18),
    'adult',
    'minor'
);

// CASE with multiple conditions
$expr->case(
    $expr->condition('score', '>=', 90),
    'A',
    $expr->case(
        $expr->condition('score', '>=', 80),
        'B',
        'C'
    )
);
```

## Grouping and Logic

### Grouping Conditions

Group conditions with parentheses:

```php
// (age > 18 AND status = 'active')
$expr->group(
    $expr->condition('age', '>', 18),
    'AND',
    $expr->condition('status', '=', 'active')
);

// Complex grouping: (a AND b) OR (c AND d)
$group1 = $expr->group(
    $expr->condition('a', '=', 1),
    'AND',
    $expr->condition('b', '=', 2)
);

$group2 = $expr->group(
    $expr->condition('c', '=', 3),
    'AND',
    $expr->condition('d', '=', 4)
);

$finalExpr = $expr->group($group1, 'OR', $group2);
```

### AND/OR Logic

```php
// Using builder methods
$dml->select('*')
    ->from('users')
    ->where($expr->condition('age', '>', 18))      // WHERE age > 18
    ->where($expr->condition('status', '=', 'active'))  // AND status = 'active'
    ->orWhere($expr->condition('role', '=', 'admin'));  // OR role = 'admin'

// Using expressions directly
$condition = $expr->group(
    $expr->condition('age', '>', 18),
    'AND',
    $expr->condition('status', '=', 'active')
);
```

## Aggregate Functions

### COUNT

```php
// COUNT(*)
$expr->count('*');

// COUNT(*) AS total
$expr->count('*', 'total');

// COUNT(DISTINCT user_id)
$expr->count('DISTINCT user_id', 'unique_users');
```

### SUM

```php
// SUM(amount)
$expr->sum('amount');

// SUM(amount) AS total_amount
$expr->sum('amount', 'total_amount');
```

### AVG

```php
// AVG(age)
$expr->avg('age');

// AVG(age) AS average_age
$expr->avg('age', 'average_age');
```

### MAX

```php
// MAX(price)
$expr->max('price');

// MAX(price) AS highest_price
$expr->max('price', 'highest_price');
```

### MIN

```php
// MIN(price)
$expr->min('price');

// MIN(price) AS lowest_price
$expr->min('price', 'lowest_price');
```

## Aliases

Create column or expression aliases:

```php
// name AS full_name
$expr->alias('full_name', 'name');

// CONCAT(first_name, ' ', last_name) AS full_name
$expr->alias('full_name', "CONCAT(first_name, ' ', last_name)");

// With expression object
$concat = $expr->raw("CONCAT(first_name, ' ', last_name)");
$expr->alias('full_name', $concat);
```

## Composing Expressions

### Reusable Components

Create reusable expression components:

```php
class UserExpressions
{
    public function __construct(private DmlManagerInterface $dml) {}
    
    public function isActive(): SqlExpressionInterface
    {
        return $this->dml->expr()->condition('status', '=', 'active');
    }
    
    public function isAdult(): SqlExpressionInterface
    {
        return $this->dml->expr()->condition('age', '>=', 18);
    }
    
    public function isPremium(): SqlExpressionInterface
    {
        return $this->dml->expr()->condition('plan', '=', 'premium');
    }
    
    public function isActiveAdult(): SqlExpressionInterface
    {
        return $this->dml->expr()->group(
            $this->isActive(),
            'AND',
            $this->isAdult()
        );
    }
}

// Usage
$userExpr = new UserExpressions($dml);

$users = $dml->select('*')
    ->from('users')
    ->where($userExpr->isActiveAdult())
    ->execute();
```

### Combining Expressions

```php
$expr1 = $dml->expr()->condition('age', '>', 18);
$expr2 = $dml->expr()->condition('status', '=', 'active');
$expr3 = $dml->expr()->condition('role', '=', 'admin');

// Combine with AND
$combined = $dml->expr()->group($expr1, 'AND', $expr2);

// Add OR condition
$final = $dml->expr()->group($combined, 'OR', $expr3);

// Use in query
$users = $dml->select('*')
    ->from('users')
    ->where($final)
    ->execute();
```

## Dynamic Expression Building

### Conditional Expressions

```php
function buildUserFilter(
    DmlManagerInterface $dml,
    ?string $status = null,
    ?int $minAge = null,
    ?string $role = null
): ?SqlExpressionInterface {
    $conditions = [];
    
    if ($status) {
        $conditions[] = $dml->expr()->condition('status', '=', $status);
    }
    
    if ($minAge) {
        $conditions[] = $dml->expr()->condition('age', '>=', $minAge);
    }
    
    if ($role) {
        $conditions[] = $dml->expr()->condition('role', '=', $role);
    }
    
    if (empty($conditions)) {
        return null;
    }
    
    // Combine all conditions with AND
    $result = array_shift($conditions);
    foreach ($conditions as $condition) {
        $result = $dml->expr()->group($result, 'AND', $condition);
    }
    
    return $result;
}

// Usage
$filter = buildUserFilter($dml, status: 'active', minAge: 18);
if ($filter) {
    $users = $dml->select('*')
        ->from('users')
        ->where($filter)
        ->execute();
}
```

### Filter Builder Class

```php
class FilterBuilder
{
    private array $conditions = [];
    
    public function __construct(
        private DmlManagerInterface $dml
    ) {}
    
    public function addCondition(string $column, string $operator, mixed $value): self
    {
        $this->conditions[] = $this->dml->expr()->condition($column, $operator, $value);
        return $this;
    }
    
    public function addIn(string $column, array $values): self
    {
        $this->conditions[] = $this->dml->expr()->in($column, $values);
        return $this;
    }
    
    public function addLike(string $column, string $pattern): self
    {
        $this->conditions[] = $this->dml->expr()->like($column, $pattern);
        return $this;
    }
    
    public function build(string $logic = 'AND'): ?SqlExpressionInterface
    {
        if (empty($this->conditions)) {
            return null;
        }
        
        $result = array_shift($this->conditions);
        foreach ($this->conditions as $condition) {
            $result = $this->dml->expr()->group($result, $logic, $condition);
        }
        
        return $result;
    }
}

// Usage
$filter = new FilterBuilder($dml);
$filter->addCondition('status', '=', 'active')
    ->addCondition('age', '>', 18)
    ->addIn('role', ['user', 'moderator']);

$users = $dml->select('*')
    ->from('users')
    ->where($filter->build())
    ->execute();
```

## Expression Chaining

Expressions support fluent chaining:

```php
$expr = $dml->expr()
    ->keyword('SELECT')
    ->identifier('users.id')
    ->keyword('FROM')
    ->identifier('users')
    ->keyword('WHERE')
    ->condition('age', '>', 18);

// Join parts together
$sql = $expr->join(' ');
```

## Raw SQL Expressions

When you need raw SQL:

```php
// Use raw SQL (use with caution!)
$expr->raw('CURRENT_TIMESTAMP');
$expr->raw('RAND()');
$expr->raw('MD5(email)');

// In a query
$users = $dml->select(
        'id',
        'name',
        $dml->expr()->alias('hash', $dml->expr()->raw('MD5(email)'))
    )
    ->from('users')
    ->execute();
```

**⚠️ Warning:** Raw SQL bypasses safety features. Only use when necessary and never with user input.

## Advanced Patterns

### Expression Factory

Create a factory for common expressions:

```php
class ExpressionFactory
{
    public function __construct(
        private DmlManagerInterface $dml
    ) {}
    
    public function dateRange(string $column, string $start, string $end): SqlExpressionInterface
    {
        return $this->dml->expr()->group(
            $this->dml->expr()->condition($column, '>=', $start),
            'AND',
            $this->dml->expr()->condition($column, '<=', $end)
        );
    }
    
    public function searchColumns(array $columns, string $term): SqlExpressionInterface
    {
        $conditions = array_map(
            fn($col) => $this->dml->expr()->like($col, "%$term%"),
            $columns
        );
        
        $result = array_shift($conditions);
        foreach ($conditions as $condition) {
            $result = $this->dml->expr()->group($result, 'OR', $condition);
        }
        
        return $result;
    }
    
    public function notDeleted(string $column = 'deleted_at'): SqlExpressionInterface
    {
        return $this->dml->expr()->condition($column, 'IS', null);
    }
}

// Usage
$factory = new ExpressionFactory($dml);

$users = $dml->select('*')
    ->from('users')
    ->where($factory->dateRange('created_at', '2024-01-01', '2024-12-31'))
    ->where($factory->notDeleted())
    ->execute();
```

### Expression Decorator

Decorate expressions with additional logic:

```php
class SecureExpression
{
    public function __construct(
        private SqlExpressionInterface $expr
    ) {}
    
    public function condition(string $column, string $operator, mixed $value): SqlExpressionInterface
    {
        // Validate column name (prevent SQL injection)
        if (!preg_match('/^[a-zA-Z0-9_.]+$/', $column)) {
            throw new InvalidArgumentException("Invalid column name: $column");
        }
        
        // Validate operator
        $allowedOperators = ['=', '!=', '<', '>', '<=', '>=', 'LIKE', 'IN'];
        if (!in_array(strtoupper($operator), $allowedOperators)) {
            throw new InvalidArgumentException("Invalid operator: $operator");
        }
        
        return $this->expr->condition($column, $operator, $value);
    }
}
```

## Performance Considerations

### Reuse Expressions

Reuse expression objects when possible:

```php
// Good - reuse expression object
$expr = $dml->expr();
$condition1 = $expr->condition('a', '=', 1);
$condition2 = $expr->condition('b', '=', 2);

// Avoid - creates many expression objects
$condition1 = $dml->expr()->condition('a', '=', 1);
$condition2 = $dml->expr()->condition('b', '=', 2);
```

### Pre-build Complex Expressions

For frequently used complex expressions:

```php
class CachedExpressions
{
    private ?SqlExpressionInterface $activeUsers = null;
    
    public function __construct(
        private DmlManagerInterface $dml
    ) {}
    
    public function activeUsers(): SqlExpressionInterface
    {
        if ($this->activeUsers === null) {
            $this->activeUsers = $this->dml->expr()->group(
                $this->dml->expr()->condition('status', '=', 'active'),
                'AND',
                $this->dml->expr()->condition('deleted_at', 'IS', null)
            );
        }
        
        return $this->activeUsers;
    }
}
```

## Testing Expressions

### Unit Testing

```php
class UserExpressionTest extends TestCase
{
    private DmlManagerInterface $dml;
    
    protected function setUp(): void
    {
        $this->dml = $this->createMock(DmlManagerInterface::class);
        // Configure mock...
    }
    
    public function testIsActiveExpression()
    {
        $expr = new UserExpressions($this->dml);
        $condition = $expr->isActive();
        
        $this->assertInstanceOf(SqlExpressionInterface::class, $condition);
        // Assert SQL output if needed
        $this->assertEquals("status = 'active'", (string) $condition);
    }
}
```

## Best Practices

1. **Always Use Expressions for User Input** - Never concatenate user input
2. **Reuse Expression Objects** - Create once, use multiple times
3. **Extract Complex Logic** - Move complex expressions to dedicated classes
4. **Type Hint Parameters** - Use PHP type hints for safer code
5. **Test Expression Logic** - Unit test complex expression builders
6. **Avoid Raw SQL** - Use raw expressions only when absolutely necessary
7. **Document Complex Expressions** - Add comments explaining business logic

## Common Patterns

### Search Filter

```php
function buildSearchFilter(DmlManagerInterface $dml, string $query): SqlExpressionInterface
{
    $term = "%$query%";
    return $dml->expr()->group(
        $dml->expr()->like('name', $term),
        'OR',
        $dml->expr()->group(
            $dml->expr()->like('email', $term),
            'OR',
            $dml->expr()->like('description', $term)
        )
    );
}
```

### Date Range

```php
function dateRangeFilter(
    DmlManagerInterface $dml,
    string $column,
    ?string $start,
    ?string $end
): ?SqlExpressionInterface {
    if (!$start && !$end) {
        return null;
    }
    
    $conditions = [];
    if ($start) {
        $conditions[] = $dml->expr()->condition($column, '>=', $start);
    }
    if ($end) {
        $conditions[] = $dml->expr()->condition($column, '<=', $end);
    }
    
    if (count($conditions) === 1) {
        return $conditions[0];
    }
    
    return $dml->expr()->group($conditions[0], 'AND', $conditions[1]);
}
```

## Next Steps

- **[Query Builders](builders.md)** - Learn how to use expressions with builders
- **[Best Practices](best-practices.md)** - Recommended patterns and practices
- **[Examples](examples.md)** - Real-world usage examples
- **[API Reference](api-reference.md)** - Complete API documentation
