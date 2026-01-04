# Best Practices

This guide covers recommended patterns, practices, and conventions for using Concept DBAL effectively.

## Architecture & Design

### 1. Use Repository Pattern

Encapsulate data access logic in repository classes:

```php
<?php
namespace App\Repository;

use Concept\DBAL\DML\DmlManagerInterface;

class UserRepository
{
    public function __construct(
        private DmlManagerInterface $dml
    ) {}
    
    public function findById(int $id): ?array
    {
        $results = $this->dml->select('*')
            ->from('users')
            ->where($this->dml->expr()->condition('id', '=', $id))
            ->limit(1)
            ->execute();
            
        return $results[0] ?? null;
    }
    
    public function findActive(): array
    {
        return $this->dml->select('*')
            ->from('users')
            ->where($this->dml->expr()->condition('status', '=', 'active'))
            ->execute();
    }
}
```

**Benefits:**
- ✅ Single source of truth for queries
- ✅ Easy to test
- ✅ Reusable logic
- ✅ Clear separation of concerns

### 2. Depend on Interfaces

Always type-hint interfaces, not concrete classes:

```php
// ✅ Good
class UserService
{
    public function __construct(
        private DmlManagerInterface $dml,
        private UserRepository $repository
    ) {}
}

// ❌ Bad
class UserService
{
    public function __construct(
        private DmlManager $dml,
        private UserRepository $repository
    ) {}
}
```

### 3. Use Dependency Injection

Let the DI container manage dependencies:

```php
// ✅ Good - Dependencies injected
class OrderService
{
    public function __construct(
        private DmlManagerInterface $dml,
        private UserRepository $userRepository,
        private ProductRepository $productRepository
    ) {}
}

// ❌ Bad - Manual instantiation
class OrderService
{
    private $dml;
    
    public function __construct()
    {
        $this->dml = new DmlManager(/* ... */);
    }
}
```

## Query Building

### 4. Use Named Methods for Complex Queries

Extract complex queries into named methods:

```php
// ✅ Good
class ReportRepository
{
    public function getMonthlyRevenue(int $year, int $month): array
    {
        return $this->dml->select(
                'DATE(order_date) as date',
                $this->dml->expr()->sum('total', 'revenue')
            )
            ->from('orders')
            ->where($this->dml->expr()->condition('YEAR(order_date)', '=', $year))
            ->where($this->dml->expr()->condition('MONTH(order_date)', '=', $month))
            ->where($this->dml->expr()->condition('status', '=', 'completed'))
            ->groupBy('DATE(order_date)')
            ->orderBy('date')
            ->execute();
    }
}

// ❌ Bad - Inline complex query in controller
$revenue = $dml->select('DATE(order_date) as date', $dml->expr()->sum('total', 'revenue'))
    ->from('orders')
    ->where($dml->expr()->condition('YEAR(order_date)', '=', $year))
    // ... more conditions
    ->execute();
```

### 5. Always Use Parameter Binding

Never concatenate user input into queries:

```php
// ✅ Good - Using expression builder (recommended)
$users = $dml->select('*')
    ->from('users')
    ->where($dml->expr()->condition('email', '=', $email))
    ->execute();

// ✅ Good - Using named placeholders with bind()
$users = $dml->select('*')
    ->from('users')
    ->where($dml->expr()->raw('email = :email'))
    ->bind(['email' => $email])
    ->execute();

// ❌ Bad - SQL injection risk
$users = $dml->select('*')
    ->from('users')
    ->where($dml->expr()->raw("email = '$email'"))  // NEVER DO THIS!
    ->execute();

// ❌ Bad - Direct string concatenation
$sql = "SELECT * FROM users WHERE email = '$email'";  // NEVER DO THIS!
```

**When to Use Named Placeholders:**

Named placeholders with `bind()` are useful when:
- Migrating from Doctrine DBAL or PDO code
- Working with complex raw SQL that can't be expressed with the builder
- You need database-specific functions or syntax

However, the expression builder is preferred for standard queries as it provides:
- Better type safety
- Database abstraction
- More readable code
- Automatic parameter handling

### 6. Use Type Hints

Leverage PHP's type system:

```php
// ✅ Good
public function findByStatus(string $status): array
{
    return $this->dml->select('*')
        ->from('users')
        ->where($this->dml->expr()->condition('status', '=', $status))
        ->execute();
}

// ❌ Bad
public function findByStatus($status)
{
    return $this->dml->select('*')
        ->from('users')
        ->where($this->dml->expr()->condition('status', '=', $status))
        ->execute();
}
```

### 7. Limit Result Sets

Always use LIMIT for potentially large result sets:

```php
// ✅ Good
$recentOrders = $this->dml->select('*')
    ->from('orders')
    ->orderBy('created_at', 'DESC')
    ->limit(100)
    ->execute();

// ❌ Bad - Could return millions of rows
$allOrders = $this->dml->select('*')
    ->from('orders')
    ->execute();
```

### 8. Use Meaningful Aliases

Make column names clear in results:

```php
// ✅ Good
$users = $dml->select(
        'users.id',
        'users.name',
        $dml->expr()->alias('total_orders', 'COUNT(orders.id)'),
        $dml->expr()->alias('total_spent', 'SUM(orders.total)')
    )
    ->from('users')
    ->leftJoin('orders', $dml->expr()->condition('users.id', '=', 'orders.user_id'))
    ->groupBy('users.id')
    ->execute();

// ❌ Bad - Unclear column names
$users = $dml->select(
        'users.id',
        'users.name',
        'COUNT(orders.id)',
        'SUM(orders.total)'
    )
    ->from('users')
    ->leftJoin('orders', $dml->expr()->condition('users.id', '=', 'orders.user_id'))
    ->groupBy('users.id')
    ->execute();
```

## Performance

### 9. Reuse Expression Objects

Create expression objects once and reuse:

```php
// ✅ Good
class UserFilters
{
    private ?SqlExpressionInterface $activeFilter = null;
    
    public function __construct(
        private DmlManagerInterface $dml
    ) {}
    
    public function active(): SqlExpressionInterface
    {
        if ($this->activeFilter === null) {
            $this->activeFilter = $this->dml->expr()
                ->condition('status', '=', 'active');
        }
        return $this->activeFilter;
    }
}

// ❌ Bad - Creates new expression each time
public function active(): SqlExpressionInterface
{
    return $this->dml->expr()->condition('status', '=', 'active');
}
```

### 10. Use Indexes Wisely

Write queries that can use database indexes:

```php
// ✅ Good - Can use index on (status, created_at)
$users = $dml->select('*')
    ->from('users')
    ->where($dml->expr()->condition('status', '=', 'active'))
    ->where($dml->expr()->condition('created_at', '>', '2024-01-01'))
    ->execute();

// ❌ Bad - Cannot use index effectively
$users = $dml->select('*')
    ->from('users')
    ->where($dml->expr()->condition('YEAR(created_at)', '=', 2024))
    ->execute();
```

### 11. Select Only Needed Columns

Don't use `SELECT *` unless you need all columns:

```php
// ✅ Good
$users = $dml->select('id', 'name', 'email')
    ->from('users')
    ->execute();

// ❌ Bad - Fetches unnecessary data
$users = $dml->select('*')
    ->from('users')
    ->execute();
```

### 12. Use Pagination for Large Data Sets

Implement pagination for user interfaces:

```php
public function getPaginated(int $page = 1, int $perPage = 20): array
{
    $offset = ($page - 1) * $perPage;
    
    return $this->dml->select('*')
        ->from('users')
        ->orderBy('id')
        ->limit($perPage)
        ->offset($offset)
        ->execute();
}
```

## Code Organization

### 13. Extract Reusable Query Components

Create reusable expression builders:

```php
// ✅ Good
class QueryFilters
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
    
    public function notDeleted(string $column = 'deleted_at'): SqlExpressionInterface
    {
        return $this->dml->expr()->condition($column, 'IS', null);
    }
}
```

### 14. Use Enums for Constants

Define query constants with enums:

```php
enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case BANNED = 'banned';
}

// Usage
$users = $dml->select('*')
    ->from('users')
    ->where($dml->expr()->condition('status', '=', UserStatus::ACTIVE->value))
    ->execute();
```

### 15. Document Complex Queries

Add comments explaining business logic:

```php
/**
 * Get users who have made a purchase in the last 30 days
 * but haven't logged in for 7 days (re-engagement candidates)
 */
public function getReengagementCandidates(): array
{
    $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
    $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
    
    return $this->dml->select('users.*')
        ->from('users')
        ->join('orders', $dml->expr()->condition('users.id', '=', 'orders.user_id'))
        ->where($dml->expr()->condition('orders.created_at', '>', $thirtyDaysAgo))
        ->where($dml->expr()->condition('users.last_login', '<', $sevenDaysAgo))
        ->groupBy('users.id')
        ->execute();
}
```

## Security

### 16. Validate User Input

Always validate input before using in queries:

```php
public function findByStatus(string $status): array
{
    // Validate input
    $validStatuses = ['active', 'inactive', 'pending'];
    if (!in_array($status, $validStatuses)) {
        throw new InvalidArgumentException("Invalid status: $status");
    }
    
    return $this->dml->select('*')
        ->from('users')
        ->where($this->dml->expr()->condition('status', '=', $status))
        ->execute();
}
```

### 17. Use Prepared Statements

Expressions automatically use prepared statements:

```php
// ✅ Good - Automatic prepared statement
$users = $dml->select('*')
    ->from('users')
    ->where($dml->expr()->condition('email', '=', $userEmail))
    ->execute();

// Internally becomes:
// SELECT * FROM users WHERE email = ?
// With parameters: [$userEmail]
```

### 18. Avoid Dynamic Table Names

Don't use user input for table names:

```php
// ❌ Bad - SQL injection risk
public function getFromTable(string $table): array
{
    return $dml->select('*')->from($table)->execute();
}

// ✅ Good - Whitelist tables
public function getFromTable(string $table): array
{
    $allowedTables = ['users', 'orders', 'products'];
    if (!in_array($table, $allowedTables)) {
        throw new InvalidArgumentException("Invalid table: $table");
    }
    
    return $dml->select('*')->from($table)->execute();
}
```

## Testing

### 19. Mock Dependencies in Tests

Use mocks for unit testing:

```php
class UserRepositoryTest extends TestCase
{
    private DmlManagerInterface $dml;
    private UserRepository $repository;
    
    protected function setUp(): void
    {
        $this->dml = $this->createMock(DmlManagerInterface::class);
        $this->repository = new UserRepository($this->dml);
    }
    
    public function testFindById(): void
    {
        $mockBuilder = $this->createMock(SelectBuilderInterface::class);
        
        $this->dml->expects($this->once())
            ->method('select')
            ->with('*')
            ->willReturn($mockBuilder);
            
        $mockBuilder->expects($this->once())
            ->method('from')
            ->with('users')
            ->willReturnSelf();
            
        // ... more expectations
        
        $result = $this->repository->findById(1);
    }
}
```

### 20. Test Query Logic

Test query building logic separately from execution:

```php
public function testBuildSearchQuery(): void
{
    $query = $this->repository->buildSearchQuery('test');
    
    $sql = $query->getSql();
    $params = $query->getBindings();
    
    $this->assertStringContainsString('WHERE', $sql);
    $this->assertStringContainsString('LIKE', $sql);
    $this->assertCount(1, $params);
}
```

## Error Handling

### 21. Handle Query Errors Gracefully

Wrap query execution in try-catch:

```php
public function findById(int $id): ?array
{
    try {
        $results = $this->dml->select('*')
            ->from('users')
            ->where($this->dml->expr()->condition('id', '=', $id))
            ->limit(1)
            ->execute();
            
        return $results[0] ?? null;
    } catch (DBALException $e) {
        // Log error
        $this->logger->error('Failed to find user', [
            'id' => $id,
            'error' => $e->getMessage()
        ]);
        
        // Rethrow or return null
        return null;
    }
}
```

### 22. Provide Meaningful Error Messages

Add context to errors:

```php
public function updateStatus(int $userId, string $status): void
{
    try {
        $this->dml->update('users')
            ->set('status', $status)
            ->where($this->dml->expr()->condition('id', '=', $userId))
            ->execute();
    } catch (DBALException $e) {
        throw new RuntimeException(
            "Failed to update user status for user {$userId} to {$status}: {$e->getMessage()}",
            previous: $e
        );
    }
}
```

## Maintenance

### 23. Use Constants for Table Names

Define table names as constants:

```php
class TableNames
{
    public const USERS = 'users';
    public const ORDERS = 'orders';
    public const PRODUCTS = 'products';
}

// Usage
$users = $dml->select('*')
    ->from(TableNames::USERS)
    ->execute();
```

### 24. Version Control Your Queries

Use version comments for tracking changes:

```php
/**
 * Get active users
 * 
 * @since 1.0.0
 * @updated 2.0.0 - Added email verification filter
 */
public function getActiveUsers(): array
{
    return $this->dml->select('*')
        ->from('users')
        ->where($this->dml->expr()->condition('status', '=', 'active'))
        ->where($this->dml->expr()->condition('email_verified', '=', true)) // Added 2.0.0
        ->execute();
}
```

### 25. Profile Slow Queries

Use EXPLAIN for complex queries:

```php
// Debug slow queries
$query = $dml->select('*')
    ->from('users')
    ->join('orders', $dml->expr()->condition('users.id', '=', 'orders.user_id'))
    ->where($dml->expr()->condition('orders.total', '>', 1000));

// Get EXPLAIN output
$explain = $dml->select('*')
    ->from('users')
    ->join('orders', $dml->expr()->condition('users.id', '=', 'orders.user_id'))
    ->where($dml->expr()->condition('orders.total', '>', 1000))
    ->explain()
    ->execute();

print_r($explain);
```

## Code Style

### 26. Consistent Formatting

Format queries for readability:

```php
// ✅ Good - Readable formatting
$users = $this->dml->select('id', 'name', 'email')
    ->from('users')
    ->where($this->dml->expr()->condition('status', '=', 'active'))
    ->where($this->dml->expr()->condition('age', '>', 18))
    ->orderBy('name', 'ASC')
    ->limit(10)
    ->execute();

// ❌ Bad - Hard to read
$users = $this->dml->select('id', 'name', 'email')->from('users')->where($this->dml->expr()->condition('status', '=', 'active'))->where($this->dml->expr()->condition('age', '>', 18))->orderBy('name', 'ASC')->limit(10)->execute();
```

### 27. Use Method Chaining Wisely

Break long chains for readability:

```php
// ✅ Good
$query = $this->dml->select('*')
    ->from('users');

if ($status) {
    $query->where($this->dml->expr()->condition('status', '=', $status));
}

if ($minAge) {
    $query->where($this->dml->expr()->condition('age', '>=', $minAge));
}

$users = $query->execute();
```

### 28. Follow PSR Standards

Adhere to PSR-1, PSR-2, and PSR-12:

```php
<?php
declare(strict_types=1);

namespace App\Repository;

use Concept\DBAL\DML\DmlManagerInterface;

final class UserRepository
{
    public function __construct(
        private readonly DmlManagerInterface $dml
    ) {}
    
    public function findAll(): array
    {
        return $this->dml->select('*')
            ->from('users')
            ->execute();
    }
}
```

## Summary

**Key Takeaways:**

1. **Use Repository Pattern** - Encapsulate data access
2. **Depend on Interfaces** - Program to interfaces, not implementations
3. **Use DI** - Let containers manage dependencies
4. **Parameterize Queries** - Never concatenate user input
5. **Type Hint Everything** - Leverage PHP's type system
6. **Limit Results** - Always use LIMIT when appropriate
7. **Optimize Performance** - Reuse expressions, use indexes
8. **Test Thoroughly** - Unit test query logic
9. **Handle Errors** - Wrap in try-catch with meaningful messages
10. **Document Complex Queries** - Explain business logic

Following these practices will lead to more maintainable, secure, and performant applications.

## Next Steps

- **[Examples](examples.md)** - See real-world implementations
- **[Query Builders](builders.md)** - Learn all builder methods
- **[SQL Expressions](expressions.md)** - Master expression system
- **[Architecture](architecture.md)** - Understand design patterns
