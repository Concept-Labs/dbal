# Query Builders Guide

This comprehensive guide covers all query builders in Concept DBAL: SELECT, INSERT, UPDATE, and DELETE.

## Table of Contents

- [Overview](#overview)
- [SelectBuilder](#selectbuilder)
- [InsertBuilder](#insertbuilder)
- [UpdateBuilder](#updatebuilder)
- [DeleteBuilder](#deletebuilder)
- [Common Features](#common-features)
- [Advanced Techniques](#advanced-techniques)

## Overview

Each query type has a dedicated builder class that provides a fluent interface for constructing SQL queries. All builders share common traits and patterns while exposing methods specific to their query type.

### Common Pattern

```php
// Get builder from DmlManager
$builder = $dml->select(...);  // Returns SelectBuilderInterface
$builder = $dml->insert(...);  // Returns InsertBuilderInterface
$builder = $dml->update(...);  // Returns UpdateBuilderInterface
$builder = $dml->delete(...);  // Returns DeleteBuilderInterface

// Chain methods
$results = $builder->method1(...)
    ->method2(...)
    ->method3(...)
    ->execute();
```

## SelectBuilder

The most feature-rich builder for retrieving data from the database.

### Basic SELECT

```php
// Select all columns
$users = $dml->select('*')
    ->from('users')
    ->execute();

// Select specific columns
$users = $dml->select('id', 'name', 'email')
    ->from('users')
    ->execute();

// Select with array
$users = $dml->select(['id', 'name', 'email'])
    ->from('users')
    ->execute();
```

### FROM Clause

```php
// Single table
$query = $dml->select('*')->from('users');

// Table with alias (array syntax)
$query = $dml->select('*')->from(['u' => 'users']);

// Table with alias (string parameters)
$query = $dml->select('u.*')->from('users', 'u');

// Multiple tables (cross join) with aliases
$query = $dml->select('*')
    ->from(['u' => 'users'], ['o' => 'orders']);

// Subquery as table (derived table) with alias
$activeUsers = $dml->select('id', 'name', 'email')
    ->from('users')
    ->where($dml->expr()->condition('status', '=', 'active'));

$query = $dml->select('au.name', 'au.email')
    ->from(['au' => $activeUsers])  // Alias 'au' for the subquery
    ->where($dml->expr()->condition('au.email', 'LIKE', '%@example.com'));
```

### WHERE Clause

```php
// Simple condition
$users = $dml->select('*')
    ->from('users')
    ->where($dml->expr()->condition('age', '>', 18))
    ->execute();

// Multiple AND conditions
$users = $dml->select('*')
    ->from('users')
    ->where($dml->expr()->condition('age', '>', 18))
    ->where($dml->expr()->condition('status', '=', 'active'))
    ->execute();

// OR conditions
$users = $dml->select('*')
    ->from('users')
    ->where($dml->expr()->condition('role', '=', 'admin'))
    ->orWhere($dml->expr()->condition('role', '=', 'moderator'))
    ->execute();

// Complex conditions with grouping
$users = $dml->select('*')
    ->from('users')
    ->where(
        $dml->expr()->group(
            $dml->expr()->condition('age', '>', 18),
            'AND',
            $dml->expr()->condition('status', '=', 'active')
        )
    )
    ->orWhere($dml->expr()->condition('role', '=', 'admin'))
    ->execute();
```

### WHERE IN

```php
$users = $dml->select('*')
    ->from('users')
    ->whereIn('status', ['active', 'pending', 'approved'])
    ->execute();

// With expression
$users = $dml->select('*')
    ->from('users')
    ->where($dml->expr()->in('status', ['active', 'pending']))
    ->execute();
```

### WHERE LIKE

```php
$users = $dml->select('*')
    ->from('users')
    ->whereLike('name', 'John%')
    ->execute();

// With expression
$users = $dml->select('*')
    ->from('users')
    ->where($dml->expr()->like('name', '%Doe%'))
    ->execute();
```

### JOINs

```php
// INNER JOIN
$results = $dml->select('users.*', 'profiles.bio')
    ->from('users')
    ->join('profiles', $dml->expr()->condition('users.id', '=', 'profiles.user_id'))
    ->execute();

// LEFT JOIN
$results = $dml->select('users.*', 'orders.total')
    ->from('users')
    ->leftJoin('orders', $dml->expr()->condition('users.id', '=', 'orders.user_id'))
    ->execute();

// RIGHT JOIN
$results = $dml->select('*')
    ->from('users')
    ->rightJoin('profiles', $dml->expr()->condition('users.id', '=', 'profiles.user_id'))
    ->execute();

// Multiple JOINs
$results = $dml->select('users.name', 'orders.total', 'products.name as product')
    ->from('users')
    ->join('orders', $dml->expr()->condition('users.id', '=', 'orders.user_id'))
    ->join('order_items', $dml->expr()->condition('orders.id', '=', 'order_items.order_id'))
    ->join('products', $dml->expr()->condition('order_items.product_id', '=', 'products.id'))
    ->execute();

// JOIN with multiple conditions
$results = $dml->select('*')
    ->from('users')
    ->join('orders', 
        $dml->expr()->group(
            $dml->expr()->condition('users.id', '=', 'orders.user_id'),
            'AND',
            $dml->expr()->condition('orders.status', '=', 'completed')
        )
    )
    ->execute();
```

### ORDER BY

```php
// Single column
$users = $dml->select('*')
    ->from('users')
    ->orderBy('created_at', 'DESC')
    ->execute();

// Multiple columns
$users = $dml->select('*')
    ->from('users')
    ->orderBy('last_name', 'ASC')
    ->orderBy('first_name', 'ASC')
    ->execute();

// Default order (ASC)
$users = $dml->select('*')
    ->from('users')
    ->orderBy('name')
    ->execute();
```

### GROUP BY

```php
// Simple grouping
$stats = $dml->select('status', $dml->expr()->count('*', 'total'))
    ->from('users')
    ->groupBy('status')
    ->execute();

// Multiple columns
$stats = $dml->select('country', 'city', $dml->expr()->count('*', 'total'))
    ->from('users')
    ->groupBy('country', 'city')
    ->execute();
```

### HAVING

```php
// Filter grouped results
$stats = $dml->select('status', $dml->expr()->count('*', 'total'))
    ->from('users')
    ->groupBy('status')
    ->having($dml->expr()->condition('COUNT(*)', '>', 10))
    ->execute();

// HAVING with IN
$stats = $dml->select('category', $dml->expr()->sum('amount', 'total'))
    ->from('transactions')
    ->groupBy('category')
    ->havingIn('category', ['food', 'transport'])
    ->execute();

// HAVING with LIKE
$stats = $dml->select('name', $dml->expr()->count('*', 'total'))
    ->from('products')
    ->groupBy('name')
    ->havingLike('name', 'Pro%')
    ->execute();
```

### LIMIT and OFFSET

```php
// Limit results
$users = $dml->select('*')
    ->from('users')
    ->limit(10)
    ->execute();

// Pagination with offset
$users = $dml->select('*')
    ->from('users')
    ->limit(10)
    ->offset(20)
    ->execute();
```

### Aggregate Functions

```php
// COUNT
$result = $dml->select($dml->expr()->count('*', 'total_users'))
    ->from('users')
    ->execute();

// SUM
$result = $dml->select($dml->expr()->sum('amount', 'total_amount'))
    ->from('orders')
    ->execute();

// AVG
$result = $dml->select($dml->expr()->avg('age', 'average_age'))
    ->from('users')
    ->execute();

// MAX
$result = $dml->select($dml->expr()->max('price', 'highest_price'))
    ->from('products')
    ->execute();

// MIN
$result = $dml->select($dml->expr()->min('price', 'lowest_price'))
    ->from('products')
    ->execute();

// Multiple aggregates
$stats = $dml->select(
        $dml->expr()->count('*', 'total'),
        $dml->expr()->avg('age', 'avg_age'),
        $dml->expr()->max('created_at', 'newest'),
        $dml->expr()->min('created_at', 'oldest')
    )
    ->from('users')
    ->execute();
```

### Column Aliases

```php
// Simple alias with expr()->alias()
$users = $dml->select(
        'id',
        $dml->expr()->alias('full_name', 'CONCAT(first_name, " ", last_name)'),
        'email'
    )
    ->from('users')
    ->execute();

// Alias with expressions
$users = $dml->select(
        $dml->expr()->alias('user_id', 'id'),
        $dml->expr()->alias('years', 'YEAR(CURRENT_DATE) - YEAR(birth_date)')
    )
    ->from('users')
    ->execute();

// Array syntax for aliases (alternative approach)
$users = $dml->select(
        'id',
        ['full_name' => 'CONCAT(first_name, " ", last_name)'],
        'email'
    )
    ->from('users')
    ->execute();

// Alias with subquery (scalar subquery)
$orderCount = $dml->select($dml->expr()->count('*'))
    ->from('orders')
    ->where($dml->expr()->condition('orders.user_id', '=', 'users.id'));

$users = $dml->select(
        'id',
        'name',
        ['order_count' => $orderCount]  // Alias for subquery
    )
    ->from('users')
    ->execute();

// Multiple aliased subqueries
$totalSpent = $dml->select($dml->expr()->sum('amount'))
    ->from('orders')
    ->where($dml->expr()->condition('orders.user_id', '=', 'users.id'));

$lastOrderDate = $dml->select($dml->expr()->max('created_at'))
    ->from('orders')
    ->where($dml->expr()->condition('orders.user_id', '=', 'users.id'));

$users = $dml->select(
        'name',
        ['total_spent' => $totalSpent],
        ['last_order' => $lastOrderDate]
    )
    ->from('users')
    ->execute();
```

### DISTINCT

```php
$countries = $dml->select('DISTINCT country')
    ->from('users')
    ->execute();
```

### UNION

```php
$query1 = $dml->select('name', 'email')
    ->from('users');

$query2 = $dml->select('name', 'email')
    ->from('customers');

$results = $query1->union($query2)->execute();

// UNION ALL
$results = $query1->unionAll($query2)->execute();
```

### DESCRIBE (Table Information)

```php
// Get table structure
$structure = $dml->select()->describe('users')->execute();
```

## InsertBuilder

Build INSERT queries for adding data to tables.

### Single Row Insert

```php
$dml->insert('users')
    ->values([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'age' => 30,
        'created_at' => date('Y-m-d H:i:s')
    ])
    ->execute();
```

### Bulk Insert

```php
$dml->insert('users')
    ->values([
        ['name' => 'John Doe', 'email' => 'john@example.com'],
        ['name' => 'Jane Smith', 'email' => 'jane@example.com'],
        ['name' => 'Bob Wilson', 'email' => 'bob@example.com']
    ])
    ->execute();
```

### INSERT IGNORE

```php
$dml->insert('users')
    ->ignore()
    ->values(['name' => 'John', 'email' => 'john@example.com'])
    ->execute();
```

### Specifying Table with INTO

```php
$dml->insert()
    ->into('users')
    ->values(['name' => 'John', 'email' => 'john@example.com'])
    ->execute();
```

## UpdateBuilder

Build UPDATE queries for modifying existing data.

### Basic Update

```php
$dml->update('users')
    ->set('status', 'inactive')
    ->where($dml->expr()->condition('last_login', '<', '2023-01-01'))
    ->execute();
```

### Update Multiple Columns

```php
// Using multiple set() calls
$dml->update('users')
    ->set('status', 'premium')
    ->set('upgraded_at', date('Y-m-d H:i:s'))
    ->where($dml->expr()->condition('id', '=', 123))
    ->execute();

// Using array
$dml->update('users')
    ->set([
        'status' => 'premium',
        'upgraded_at' => date('Y-m-d H:i:s'),
        'plan' => 'pro'
    ])
    ->where($dml->expr()->condition('id', '=', 123))
    ->execute();
```

### Update with Expressions

```php
// Increment value
$dml->update('products')
    ->set('views', 'views + 1')
    ->where($dml->expr()->condition('id', '=', $productId))
    ->execute();

// Use SQL functions
$dml->update('users')
    ->set('last_login', 'NOW()')
    ->where($dml->expr()->condition('id', '=', $userId))
    ->execute();
```

### Update with JOINs

```php
$dml->update('orders')
    ->join('users', $dml->expr()->condition('orders.user_id', '=', 'users.id'))
    ->set('orders.status', 'vip')
    ->where($dml->expr()->condition('users.level', '=', 'premium'))
    ->execute();
```

### Update with LIMIT

```php
$dml->update('users')
    ->set('notified', true)
    ->where($dml->expr()->condition('notified', '=', false))
    ->limit(100)
    ->execute();
```

## DeleteBuilder

Build DELETE queries for removing data.

### Basic Delete

```php
$dml->delete('users')
    ->where($dml->expr()->condition('status', '=', 'deleted'))
    ->execute();
```

### Delete with Multiple Conditions

```php
$dml->delete('users')
    ->where($dml->expr()->condition('status', '=', 'inactive'))
    ->where($dml->expr()->condition('last_login', '<', '2020-01-01'))
    ->execute();
```

### Delete with LIMIT

```php
$dml->delete('log_entries')
    ->where($dml->expr()->condition('created_at', '<', '2020-01-01'))
    ->limit(1000)
    ->execute();
```

### Delete All (Use with Caution!)

```php
// Delete all records from table
$dml->delete('temporary_data')->execute();
```

## Common Features

### Builder Objects as Parameters

One of the most powerful features of Concept DBAL is that **builder objects can be passed as parameters to other builder methods**. This enables you to construct complex queries like subqueries, derived tables, and scalar subselects.

#### How It Works

Builder objects implement `SqlExpressionInterface` through the `asExpression()` method, which means they can be used anywhere an expression is accepted. When you pass a builder to another builder method, it automatically converts to the appropriate SQL expression.

**Methods that accept builder objects:**
- `whereIn()` - Subquery in WHERE IN clause
- `where()` - Subquery in WHERE conditions
- `from()` - Derived tables (subqueries in FROM)
- `join()`, `leftJoin()`, `rightJoin()` - Join to subquery results
- `select()` - Scalar subqueries in SELECT list
- `with()` - Common Table Expressions (CTEs)
- `union()` - Union with another query
- Expression methods like `condition()`, `in()`, `alias()`

#### Basic Pattern

```php
// Create a subquery builder
$subquery = $dml->select('column')
    ->from('table')
    ->where($dml->expr()->condition('status', '=', 'active'));

// Use it in another query
$results = $dml->select('*')
    ->from('main_table')
    ->whereIn('id', $subquery)  // Pass builder directly
    ->execute();
```

The builder automatically wraps the subquery in parentheses and generates the proper SQL.

#### Using Aliases with Builder Objects

**Most builder methods support aliases when working with builder objects**. Use array syntax `['alias' => $builder]` to assign an alias to a subquery or expression.

```php
// Alias in FROM clause (derived table)
$subquery = $dml->select('id', 'name', 'total')
    ->from('orders')
    ->where($dml->expr()->condition('status', '=', 'completed'));

$results = $dml->select('o.name', 'o.total')
    ->from(['o' => $subquery])  // Alias 'o' for the subquery
    ->where($dml->expr()->condition('o.total', '>', 100))
    ->execute();

// Alias in SELECT clause (scalar subquery)
$orderCount = $dml->select($dml->expr()->count('*'))
    ->from('orders')
    ->where($dml->expr()->condition('orders.user_id', '=', 'users.id'));

$users = $dml->select(
        'name',
        'email',
        ['total_orders' => $orderCount]  // Alias for subquery result
    )
    ->from('users')
    ->execute();

// Alias in JOIN
$recentOrders = $dml->select('user_id', $dml->expr()->sum('amount', 'total'))
    ->from('orders')
    ->where($dml->expr()->condition('created_at', '>', '2024-01-01'))
    ->groupBy('user_id');

$users = $dml->select('u.name', 'ro.total')
    ->from(['u' => 'users'])
    ->join(['ro' => $recentOrders], 'ro',  // Alias 'ro' for subquery
        $dml->expr()->condition('u.id', '=', 'ro.user_id'))
    ->execute();
```

**Key Points:**
- Use array syntax `['alias' => $value]` to specify aliases
- Aliases work with strings, expressions, and builder objects
- Common in `select()`, `from()`, `join()` methods
- Aliases make complex queries more readable and maintainable

### Understanding the Alias Pattern

Concept DBAL uses a consistent pattern for aliases across all builder methods. There are two ways to specify aliases:

#### 1. Array Syntax (Recommended)

Use associative arrays where the key is the alias and the value is the column, table, expression, or builder:

```php
// In SELECT
$dml->select(
    'id',                           // No alias
    ['user_name' => 'name'],        // Alias for column
    ['total' => $subquery]          // Alias for subquery
);

// In FROM
$dml->select('*')
    ->from(['u' => 'users'])        // Alias 'u' for table
    ->from(['orders_sub' => $orderBuilder]); // Alias for subquery

// In JOIN
$dml->select('*')
    ->from('users')
    ->join(['o' => 'orders'], 'o', $condition)  // Alias 'o' for table
    ->leftJoin(['stats' => $statsBuilder], 'stats', $condition); // Alias for subquery
```

#### 2. Expression Method (Alternative)

Use the `alias()` method from the expression builder:

```php
$dml->select(
    $dml->expr()->alias('user_name', 'name'),
    $dml->expr()->alias('full_name', 'CONCAT(first_name, " ", last_name)')
);
```

#### Methods Supporting Aliases

| Method | Alias Support | Example |
|--------|---------------|---------|
| `select()` | ✅ Yes | `select(['alias' => 'column'])` or `select(['alias' => $builder])` |
| `from()` | ✅ Yes | `from(['alias' => 'table'])` or `from(['alias' => $builder])` |
| `join()` | ✅ Yes | `join(['alias' => 'table'], 'alias', ...)` or with builder |
| `leftJoin()` | ✅ Yes | `leftJoin(['alias' => $builder], 'alias', ...)` |
| `rightJoin()` | ✅ Yes | `rightJoin(['alias' => 'table'], 'alias', ...)` |
| `groupBy()` | ✅ Yes | `groupBy(['alias' => 'column'])` |
| `orderBy()` | ✅ Yes | `orderBy(['alias' => 'column'], 'ASC')` |
| `with()` | ✅ Yes (via name parameter) | `with('cte_alias', $builder)` |

#### Practical Examples

```php
// Complex query with multiple aliases
$orderStats = $dml->select(
        'user_id',
        $dml->expr()->count('*', 'order_count'),
        $dml->expr()->sum('total', 'total_spent')
    )
    ->from('orders')
    ->where($dml->expr()->condition('status', '=', 'completed'))
    ->groupBy('user_id');

$userProfiles = $dml->select('user_id', 'bio', 'avatar')
    ->from('profiles')
    ->where($dml->expr()->condition('is_public', '=', true));

$results = $dml->select(
        ['username' => 'u.name'],           // Column alias
        ['email' => 'u.email'],
        ['orders' => 'os.order_count'],     // Reference to subquery alias
        ['spent' => 'os.total_spent'],
        ['bio' => 'up.bio']
    )
    ->from(['u' => 'users'])                // Table alias
    ->leftJoin(['os' => $orderStats], 'os', // Subquery alias 'os'
        $dml->expr()->condition('u.id', '=', 'os.user_id'))
    ->leftJoin(['up' => $userProfiles], 'up', // Subquery alias 'up'
        $dml->expr()->condition('u.id', '=', 'up.user_id'))
    ->where($dml->expr()->condition('u.status', '=', 'active'))
    ->execute();
```

### Getting SQL Without Executing

```php
// Build query
$query = $dml->select('*')
    ->from('users')
    ->where($dml->expr()->condition('status', '=', 'active'));

// Get SQL string
$sql = $query->getSql();
echo $sql;

// Get bound parameters
$params = $query->getParams();
print_r($params);
```

### Resetting Query Parts

```php
$query = $dml->select('*')
    ->from('users')
    ->where($dml->expr()->condition('age', '>', 18));

// Reset WHERE clause
$query->resetSection(KeywordEnum::WHERE)
    ->where($dml->expr()->condition('status', '=', 'active'));
```

### Cloning Queries

```php
// Base query
$baseQuery = $dml->select('*')->from('users');

// Clone and modify
$activeUsers = clone $baseQuery;
$activeUsers->where($dml->expr()->condition('status', '=', 'active'));

$inactiveUsers = clone $baseQuery;
$inactiveUsers->where($dml->expr()->condition('status', '=', 'inactive'));
```

## Advanced Techniques

### Subqueries

Subqueries are queries nested inside other queries. You can use builder objects as subqueries in various parts of your SQL statements.

#### Subquery in WHERE IN

```php
// Find users who have placed orders over $1000
$subquery = $dml->select('user_id')
    ->from('orders')
    ->where($dml->expr()->condition('total', '>', 1000));

$users = $dml->select('*')
    ->from('users')
    ->whereIn('id', $subquery)
    ->execute();
```

#### Subquery in WHERE Condition

```php
// Find users with above-average age
$avgAgeSubquery = $dml->select($dml->expr()->avg('age'))
    ->from('users');

$users = $dml->select('*')
    ->from('users')
    ->where($dml->expr()->condition('age', '>', $avgAgeSubquery))
    ->execute();
```

#### Scalar Subquery in SELECT

```php
// Select user with their total order count
$orderCountSubquery = $dml->select($dml->expr()->count('*'))
    ->from('orders')
    ->where($dml->expr()->condition('orders.user_id', '=', 'users.id'));

$users = $dml->select(
        'name',
        'email',
        $dml->expr()->alias('order_count', $orderCountSubquery)
    )
    ->from('users')
    ->execute();
```

#### Derived Tables (Subquery in FROM)

```php
// Query from a subquery result (derived table) with alias
$recentOrders = $dml->select('user_id', 'total', 'created_at')
    ->from('orders')
    ->where($dml->expr()->condition('created_at', '>', '2024-01-01'))
    ->orderBy('created_at', 'DESC');

// Use array syntax to assign alias 'ro' to the subquery
$summary = $dml->select(
        'ro.user_id',
        $dml->expr()->sum('ro.total', 'total_sales'),
        $dml->expr()->count('*', 'order_count')
    )
    ->from(['ro' => $recentOrders])  // IMPORTANT: Use array syntax for alias
    ->groupBy('ro.user_id')
    ->execute();

// Multiple derived tables with aliases
$activeUsers = $dml->select('id', 'name')
    ->from('users')
    ->where($dml->expr()->condition('status', '=', 'active'));

$completedOrders = $dml->select('user_id', $dml->expr()->count('*', 'order_count'))
    ->from('orders')
    ->where($dml->expr()->condition('status', '=', 'completed'))
    ->groupBy('user_id');

$results = $dml->select('au.name', 'co.order_count')
    ->from(
        ['au' => $activeUsers],      // Alias 'au'
        ['co' => $completedOrders]   // Alias 'co'
    )
    ->where($dml->expr()->condition('au.id', '=', 'co.user_id'))
    ->execute();
```

#### Subquery in JOIN

```php
// Join with a subquery result using alias
$topCustomers = $dml->select('user_id', $dml->expr()->sum('total', 'total_spent'))
    ->from('orders')
    ->groupBy('user_id')
    ->having($dml->expr()->condition('SUM(total)', '>', 5000));

$users = $dml->select('users.name', 'tc.total_spent')
    ->from('users')
    ->join(['tc' => $topCustomers], 'tc',  // Assign alias 'tc' to subquery
        $dml->expr()->condition('users.id', '=', 'tc.user_id'))
    ->execute();

// Complex join with multiple subqueries
$userOrders = $dml->select('user_id', $dml->expr()->count('*', 'count'))
    ->from('orders')
    ->groupBy('user_id');

$userReviews = $dml->select('user_id', $dml->expr()->count('*', 'count'))
    ->from('reviews')
    ->groupBy('user_id');

$results = $dml->select('u.name', 'uo.count as order_count', 'ur.count as review_count')
    ->from(['u' => 'users'])
    ->leftJoin(['uo' => $userOrders], 'uo',
        $dml->expr()->condition('u.id', '=', 'uo.user_id'))
    ->leftJoin(['ur' => $userReviews], 'ur',
        $dml->expr()->condition('u.id', '=', 'ur.user_id'))
    ->execute();
```

#### Multiple Subqueries

```php
// Complex query with multiple subqueries
$activeOrdersSubquery = $dml->select($dml->expr()->count('*'))
    ->from('orders')
    ->where($dml->expr()->condition('orders.user_id', '=', 'users.id'))
    ->where($dml->expr()->condition('orders.status', '=', 'active'));

$avgOrderValueSubquery = $dml->select($dml->expr()->avg('total'))
    ->from('orders')
    ->where($dml->expr()->condition('orders.user_id', '=', 'users.id'));

$users = $dml->select(
        'name',
        'email',
        $dml->expr()->alias('active_orders', $activeOrdersSubquery),
        $dml->expr()->alias('avg_order_value', $avgOrderValueSubquery)
    )
    ->from('users')
    ->where($dml->expr()->condition('status', '=', 'active'))
    ->execute();
```

### Common Table Expressions (CTEs)

CTEs, also known as WITH clauses, let you define temporary named result sets that can be referenced in the main query. They're great for improving query readability and reusing subquery results.

```php
// Simple CTE
$activeUsersQuery = $dml->select('id', 'name', 'email')
    ->from('users')
    ->where($dml->expr()->condition('status', '=', 'active'));

$results = $dml->select('*')
    ->with('active_users', $activeUsersQuery)
    ->from('active_users')
    ->where($dml->expr()->condition('email', 'LIKE', '%@example.com'))
    ->execute();

// Multiple CTEs
$recentOrdersQuery = $dml->select('user_id', $dml->expr()->sum('total', 'total'))
    ->from('orders')
    ->where($dml->expr()->condition('created_at', '>', '2024-01-01'))
    ->groupBy('user_id');

$premiumUsersQuery = $dml->select('id', 'name')
    ->from('users')
    ->where($dml->expr()->condition('subscription', '=', 'premium'));

$results = $dml->select('pu.name', 'ro.total')
    ->with('recent_orders', $recentOrdersQuery)
    ->with('premium_users', $premiumUsersQuery)
    ->from('premium_users', 'pu')
    ->join('recent_orders', 'ro', 
        $dml->expr()->condition('pu.id', '=', 'ro.user_id'))
    ->execute();
```

### Complex Conditions

```php
// (status = 'active' AND age > 18) OR (role = 'admin')
$users = $dml->select('*')
    ->from('users')
    ->where(
        $dml->expr()->group(
            $dml->expr()->condition('status', '=', 'active'),
            'AND',
            $dml->expr()->condition('age', '>', 18)
        )
    )
    ->orWhere($dml->expr()->condition('role', '=', 'admin'))
    ->execute();
```

### Dynamic Query Building

```php
$query = $dml->select('*')->from('users');

// Add conditions dynamically
if ($status) {
    $query->where($dml->expr()->condition('status', '=', $status));
}

if ($minAge) {
    $query->where($dml->expr()->condition('age', '>=', $minAge));
}

if ($sortBy) {
    $query->orderBy($sortBy, $sortDir ?? 'ASC');
}

if ($limit) {
    $query->limit($limit);
    if ($offset) {
        $query->offset($offset);
    }
}

$results = $query->execute();
```

### Reusable Query Components

```php
// Create reusable condition builder
class QueryFilters
{
    public function __construct(private DmlManagerInterface $dml) {}
    
    public function activeUsers(): SqlExpressionInterface
    {
        return $this->dml->expr()->condition('status', '=', 'active');
    }
    
    public function adults(): SqlExpressionInterface
    {
        return $this->dml->expr()->condition('age', '>=', 18);
    }
}

// Use in queries
$filters = new QueryFilters($dml);

$users = $dml->select('*')
    ->from('users')
    ->where($filters->activeUsers())
    ->where($filters->adults())
    ->execute();
```

## Best Practices

1. **Always Use Parameterized Queries** - Never concatenate user input directly into SQL
2. **Use Type Hints** - Leverage PHP's type system for safer code
3. **Extract Complex Queries** - Move complex queries to repository methods
4. **Test Queries** - Unit test your query building logic
5. **Use Aliases** - Make column names clear in results
6. **Index Aware** - Be mindful of database indexes when writing WHERE clauses
7. **Limit Results** - Always use LIMIT for potentially large result sets
8. **Profile Queries** - Use EXPLAIN to understand query performance

## Next Steps

- **[SQL Expressions](expressions.md)** - Deep dive into expression system
- **[Best Practices](best-practices.md)** - Learn recommended patterns
- **[Examples](examples.md)** - See real-world query examples
- **[API Reference](api-reference.md)** - Complete method documentation
