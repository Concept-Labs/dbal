# Quick Start Guide

This guide will help you start building queries with Concept DBAL in minutes.

## Prerequisites

Make sure you have:
- Installed Concept DBAL (see [Installation Guide](installation.md))
- A working database connection
- Basic SQL knowledge

## Your First Query

### SELECT Query

The most common operation is retrieving data. Here's how to build a SELECT query:

```php
use Concept\DBAL\DML\DmlManagerInterface;

// Assuming $dml is injected via DI
$results = $dml->select('id', 'name', 'email')
    ->from('users')
    ->execute();

foreach ($results as $row) {
    echo $row['name'] . "\n";
}
```

### With WHERE Conditions

Add conditions to filter results:

```php
$users = $dml->select('id', 'name', 'email')
    ->from('users')
    ->where($dml->expr()->condition('age', '>', 18))
    ->execute();
```

### Multiple Conditions

Combine multiple conditions:

```php
$users = $dml->select('*')
    ->from('users')
    ->where($dml->expr()->condition('age', '>', 18))
    ->andWhere($dml->expr()->condition('status', '=', 'active'))
    ->execute();
```

## Common Query Patterns

### Selecting All Columns

```php
// Select all columns
$users = $dml->select('*')
    ->from('users')
    ->execute();

// Or use no arguments
$users = $dml->select()
    ->from('users')
    ->execute();
```

### Selecting with Aliases

```php
$users = $dml->select(
        'id',
        $dml->expr()->alias('full_name', 'CONCAT(first_name, " ", last_name)'),
        'email'
    )
    ->from('users')
    ->execute();
```

### Ordering Results

```php
$users = $dml->select('*')
    ->from('users')
    ->orderBy('created_at', 'DESC')
    ->execute();

// Multiple order clauses
$users = $dml->select('*')
    ->from('users')
    ->orderBy('last_name', 'ASC')
    ->orderBy('first_name', 'ASC')
    ->execute();
```

### Limiting Results

```php
// Get first 10 users
$users = $dml->select('*')
    ->from('users')
    ->limit(10)
    ->execute();

// With offset (pagination)
$users = $dml->select('*')
    ->from('users')
    ->limit(10)
    ->offset(20)
    ->execute();
```

## INSERT Operations

### Single Insert

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

## UPDATE Operations

### Basic Update

```php
$dml->update('users')
    ->set('status', 'inactive')
    ->where($dml->expr()->condition('last_login', '<', '2023-01-01'))
    ->execute();
```

### Multiple Fields

```php
$dml->update('users')
    ->set('status', 'premium')
    ->set('upgraded_at', date('Y-m-d H:i:s'))
    ->where($dml->expr()->condition('id', '=', 123))
    ->execute();

// Or use an array
$dml->update('users')
    ->set([
        'status' => 'premium',
        'upgraded_at' => date('Y-m-d H:i:s')
    ])
    ->where($dml->expr()->condition('id', '=', 123))
    ->execute();
```

## DELETE Operations

### Simple Delete

```php
$dml->delete('users')
    ->where($dml->expr()->condition('status', '=', 'deleted'))
    ->execute();
```

### Delete with Multiple Conditions

```php
$dml->delete('users')
    ->where($dml->expr()->condition('status', '=', 'inactive'))
    ->andWhere($dml->expr()->condition('last_login', '<', '2020-01-01'))
    ->execute();
```

## Working with Expressions

### Comparison Operators

```php
$expr = $dml->expr();

// Equal
$expr->condition('status', '=', 'active');

// Not equal
$expr->condition('status', '!=', 'deleted');

// Greater than
$expr->condition('age', '>', 18);

// Less than or equal
$expr->condition('age', '<=', 65);

// LIKE
$expr->like('name', 'John%');

// IN
$expr->in('status', ['active', 'pending', 'approved']);
```

### Complex Conditions

```php
// (age > 18 AND status = 'active') OR role = 'admin'
$dml->select('*')
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

## JOINs

### INNER JOIN

```php
$users = $dml->select('users.*', 'profiles.bio')
    ->from('users')
    ->join('profiles', $dml->expr()->condition('users.id', '=', 'profiles.user_id'))
    ->execute();
```

### LEFT JOIN

```php
$users = $dml->select('users.*', 'orders.total')
    ->from('users')
    ->leftJoin('orders', $dml->expr()->condition('users.id', '=', 'orders.user_id'))
    ->execute();
```

### Multiple JOINs

```php
$data = $dml->select('users.name', 'orders.total', 'products.name as product_name')
    ->from('users')
    ->join('orders', $dml->expr()->condition('users.id', '=', 'orders.user_id'))
    ->join('order_items', $dml->expr()->condition('orders.id', '=', 'order_items.order_id'))
    ->join('products', $dml->expr()->condition('order_items.product_id', '=', 'products.id'))
    ->execute();
```

## Grouping and Aggregation

### GROUP BY

```php
$stats = $dml->select('status', $dml->expr()->count('*', 'total'))
    ->from('users')
    ->groupBy('status')
    ->execute();
```

### HAVING

```php
$stats = $dml->select('status', $dml->expr()->count('*', 'total'))
    ->from('users')
    ->groupBy('status')
    ->having($dml->expr()->condition('COUNT(*)', '>', 10))
    ->execute();
```

### Aggregate Functions

```php
$stats = $dml->select(
        $dml->expr()->count('*', 'total_users'),
        $dml->expr()->avg('age', 'average_age'),
        $dml->expr()->max('created_at', 'newest_user'),
        $dml->expr()->min('created_at', 'oldest_user')
    )
    ->from('users')
    ->execute();
```

## Building Queries (Without Executing)

Sometimes you need the SQL string without executing:

```php
$query = $dml->select('*')
    ->from('users')
    ->where($dml->expr()->condition('status', '=', 'active'));

// Get the SQL string
$sql = $query->getSql();
echo $sql;  // Outputs: SELECT * FROM users WHERE status = 'active'

// Get with parameters
$params = $query->getParams();
print_r($params);
```

## Practical Example: User Repository

Here's a complete example of a repository class:

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
    
    public function findActiveUsers(int $limit = 10, int $offset = 0): array
    {
        return $this->dml->select('id', 'name', 'email', 'created_at')
            ->from('users')
            ->where($this->dml->expr()->condition('status', '=', 'active'))
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->offset($offset)
            ->execute();
    }
    
    public function countByStatus(string $status): int
    {
        $result = $this->dml->select($this->dml->expr()->count('*', 'total'))
            ->from('users')
            ->where($this->dml->expr()->condition('status', '=', $status))
            ->execute();
            
        return (int) $result[0]['total'];
    }
    
    public function create(array $data): int
    {
        $this->dml->insert('users')
            ->values($data)
            ->execute();
            
        // Get last insert ID from connection
        return $this->dml->getConnection()->lastInsertId();
    }
    
    public function update(int $id, array $data): bool
    {
        return $this->dml->update('users')
            ->set($data)
            ->where($this->dml->expr()->condition('id', '=', $id))
            ->execute();
    }
    
    public function delete(int $id): bool
    {
        return $this->dml->delete('users')
            ->where($this->dml->expr()->condition('id', '=', $id))
            ->execute();
    }
}
```

## Next Steps

Now that you know the basics:

- **[Query Builders Guide](builders.md)** - Learn all builder methods in detail
- **[SQL Expressions](expressions.md)** - Deep dive into expression system
- **[Best Practices](best-practices.md)** - Learn recommended patterns
- **[Examples](examples.md)** - See more real-world examples
- **[API Reference](api-reference.md)** - Complete method documentation
