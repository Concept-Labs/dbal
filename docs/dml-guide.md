# DML (Data Manipulation Language) Guide

The DML layer provides a fluent interface for building and executing data manipulation queries.

## Table of Contents

1. [SELECT Queries](#select-queries)
2. [INSERT Queries](#insert-queries)
3. [UPDATE Queries](#update-queries)
4. [DELETE Queries](#delete-queries)
5. [Advanced Features](#advanced-features)

## SELECT Queries

### Basic SELECT

```php
$dml = $dbalManager->dml();

// Select all columns
$result = $dml->select('*')
    ->from('users')
    ->execute();

// Select specific columns
$result = $dml->select('id', 'name', 'email')
    ->from('users')
    ->execute();

// Select with alias
$result = $dml->select('name AS full_name', 'email AS contact')
    ->from('users')
    ->execute();
```

### WHERE Conditions

```php
// Simple condition
$dml->select('*')
    ->from('users')
    ->where('status', '=', 'active')
    ->execute();

// Multiple conditions (AND)
$dml->select('*')
    ->from('users')
    ->where('status', '=', 'active')
    ->where('role', '=', 'admin')
    ->execute();

// OR conditions
$dml->select('*')
    ->from('users')
    ->where('status', '=', 'active')
    ->orWhere('role', '=', 'admin')
    ->execute();

// IN clause
$dml->select('*')
    ->from('users')
    ->whereIn('id', [1, 2, 3, 4, 5])
    ->execute();

// BETWEEN clause
$dml->select('*')
    ->from('orders')
    ->whereBetween('created_at', '2024-01-01', '2024-12-31')
    ->execute();

// NULL checks
$dml->select('*')
    ->from('users')
    ->whereNull('deleted_at')
    ->execute();

// LIKE clause
$dml->select('*')
    ->from('users')
    ->whereLike('email', '%@example.com')
    ->execute();
```

### JOIN Operations

```php
// INNER JOIN
$dml->select('users.*, orders.total')
    ->from('users')
    ->join('orders', 'users.id', '=', 'orders.user_id')
    ->execute();

// LEFT JOIN
$dml->select('users.*, orders.total')
    ->from('users')
    ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
    ->execute();

// RIGHT JOIN
$dml->select('users.*, orders.total')
    ->from('users')
    ->rightJoin('orders', 'users.id', '=', 'orders.user_id')
    ->execute();

// Multiple joins
$dml->select('users.name', 'orders.total', 'products.name')
    ->from('users')
    ->join('orders', 'users.id', '=', 'orders.user_id')
    ->join('order_items', 'orders.id', '=', 'order_items.order_id')
    ->join('products', 'order_items.product_id', '=', 'products.id')
    ->execute();
```

### Aggregation

```php
// COUNT
$result = $dml->select('COUNT(*) AS total')
    ->from('users')
    ->execute();

// SUM, AVG, MIN, MAX
$result = $dml->select('SUM(total) AS sum', 'AVG(total) AS avg')
    ->from('orders')
    ->execute();

// GROUP BY
$result = $dml->select('user_id', 'COUNT(*) AS order_count')
    ->from('orders')
    ->groupBy('user_id')
    ->execute();

// HAVING
$result = $dml->select('user_id', 'COUNT(*) AS order_count')
    ->from('orders')
    ->groupBy('user_id')
    ->having('COUNT(*)', '>', 5)
    ->execute();
```

### Ordering and Limiting

```php
// ORDER BY
$dml->select('*')
    ->from('users')
    ->orderBy('created_at', 'DESC')
    ->execute();

// Multiple ORDER BY
$dml->select('*')
    ->from('users')
    ->orderBy('status', 'ASC')
    ->orderBy('created_at', 'DESC')
    ->execute();

// LIMIT and OFFSET
$dml->select('*')
    ->from('users')
    ->limit(10)
    ->offset(20)
    ->execute();
```

### UNION Operations

```php
$query1 = $dml->select('id', 'name')->from('active_users');
$query2 = $dml->select('id', 'name')->from('inactive_users');

$result = $query1->union($query2)->execute();

// UNION ALL
$result = $query1->unionAll($query2)->execute();
```

## INSERT Queries

### Basic INSERT

```php
// Single row
$dml->insert('users')
    ->values([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'status' => 'active'
    ])
    ->execute();

// Multiple rows
$dml->insert('users')
    ->values([
        ['name' => 'John', 'email' => 'john@example.com'],
        ['name' => 'Jane', 'email' => 'jane@example.com']
    ])
    ->execute();
```

### INSERT with Options

```php
// INSERT IGNORE
$dml->insert('users')
    ->ignore()
    ->values(['name' => 'John', 'email' => 'john@example.com'])
    ->execute();

// ON DUPLICATE KEY UPDATE
$dml->insert('users')
    ->values(['id' => 1, 'name' => 'John', 'email' => 'john@example.com'])
    ->onDuplicateKeyUpdate(['name' => 'John Updated'])
    ->execute();
```

## UPDATE Queries

```php
// Simple UPDATE
$dml->update('users')
    ->set('status', 'inactive')
    ->where('last_login', '<', '2023-01-01')
    ->execute();

// Multiple columns
$dml->update('users')
    ->set([
        'status' => 'inactive',
        'updated_at' => 'NOW()'
    ])
    ->where('id', '=', 1)
    ->execute();
```

## DELETE Queries

```php
// Simple DELETE
$dml->delete('users')
    ->where('status', '=', 'deleted')
    ->execute();

// DELETE with JOIN (MySQL)
$dml->delete('users')
    ->using('users', 'orders')
    ->where('users.id', '=', 'orders.user_id')
    ->where('orders.status', '=', 'cancelled')
    ->execute();
```

## Advanced Features

### Subqueries

```php
$subquery = $dml->select('id')
    ->from('inactive_users');

$dml->select('*')
    ->from('users')
    ->whereNotIn('id', $subquery)
    ->execute();
```

### Common Table Expressions (CTE)

```php
$cte = $dml->select('user_id', 'COUNT(*) AS order_count')
    ->from('orders')
    ->groupBy('user_id');

$dml->with('user_orders', $cte)
    ->select('users.name', 'user_orders.order_count')
    ->from('users')
    ->join('user_orders', 'users.id', '=', 'user_orders.user_id')
    ->execute();
```

### Window Functions

```php
$dml->select('name', 'salary', 'RANK() OVER (ORDER BY salary DESC) AS rank')
    ->from('employees')
    ->execute();
```

### Locking

```php
// FOR UPDATE
$dml->select('*')
    ->from('inventory')
    ->where('product_id', '=', 123)
    ->forUpdate()
    ->execute();

// LOCK IN SHARE MODE
$dml->select('*')
    ->from('inventory')
    ->where('product_id', '=', 123)
    ->lockInShareMode()
    ->execute();
```

### Raw Expressions

```php
$dml->select('*')
    ->from('users')
    ->where('created_at', '>', $dml->raw('DATE_SUB(NOW(), INTERVAL 30 DAY)'))
    ->execute();
```

### EXPLAIN Queries

```php
$explanation = $dml->select('*')
    ->from('users')
    ->where('status', '=', 'active')
    ->explain()
    ->execute();
```

## Best Practices

1. **Use Parameter Binding**: The builders automatically handle parameter binding to prevent SQL injection
2. **Use Transactions**: Wrap multiple operations in transactions for data consistency
3. **Index Your Queries**: Use EXPLAIN to analyze query performance
4. **Limit Result Sets**: Always use LIMIT when fetching large datasets
5. **Use Prepared Statements**: The builders reuse prepared statements for better performance

## Error Handling

```php
try {
    $dml->insert('users')
        ->values(['name' => 'John'])
        ->execute();
} catch (\Concept\DBAL\Exception\DBALException $e) {
    // Handle database error
    error_log($e->getMessage());
}
```
