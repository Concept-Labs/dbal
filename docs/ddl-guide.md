# DDL (Data Definition Language) Guide

The DDL layer provides a fluent interface for building and executing schema definition queries.

## Table of Contents

1. [CREATE TABLE](#create-table)
2. [ALTER TABLE](#alter-table)
3. [DROP TABLE](#drop-table)
4. [TRUNCATE TABLE](#truncate-table)
5. [Best Practices](#best-practices)

## CREATE TABLE

### Basic Table Creation

```php
$ddl = $dbalManager->ddl();

// Simple table
$ddl->createTable('users')
    ->addColumn('id', 'INT')
    ->addColumn('name', 'VARCHAR(255)')
    ->execute();
```

### Complete Table Definition

```php
$ddl->createTable('users')
    ->ifNotExists()
    ->addColumn('id', 'INT', ['AUTO_INCREMENT'])
    ->addColumn('username', 'VARCHAR(50)', ['NOT NULL'])
    ->addColumn('email', 'VARCHAR(255)', ['NOT NULL'])
    ->addColumn('password', 'VARCHAR(255)', ['NOT NULL'])
    ->addColumn('first_name', 'VARCHAR(100)')
    ->addColumn('last_name', 'VARCHAR(100)')
    ->addColumn('status', 'ENUM("active", "inactive", "banned")', ['DEFAULT "active"'])
    ->addColumn('created_at', 'TIMESTAMP', ['DEFAULT CURRENT_TIMESTAMP'])
    ->addColumn('updated_at', 'TIMESTAMP', ['DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'])
    ->primaryKey('id')
    ->unique('username')
    ->unique('email')
    ->index(['status', 'created_at'], 'idx_status_created')
    ->options([
        'ENGINE' => 'InnoDB',
        'CHARSET' => 'utf8mb4',
        'COLLATE' => 'utf8mb4_unicode_ci'
    ])
    ->execute();
```

### Column Data Types

Common MySQL data types:

```php
// Numeric types
->addColumn('int_col', 'INT')
->addColumn('bigint_col', 'BIGINT')
->addColumn('decimal_col', 'DECIMAL(10,2)')
->addColumn('float_col', 'FLOAT')
->addColumn('double_col', 'DOUBLE')

// String types
->addColumn('varchar_col', 'VARCHAR(255)')
->addColumn('text_col', 'TEXT')
->addColumn('char_col', 'CHAR(10)')
->addColumn('enum_col', 'ENUM("option1", "option2")')

// Date and time types
->addColumn('date_col', 'DATE')
->addColumn('datetime_col', 'DATETIME')
->addColumn('timestamp_col', 'TIMESTAMP')
->addColumn('time_col', 'TIME')
->addColumn('year_col', 'YEAR')

// Binary types
->addColumn('blob_col', 'BLOB')
->addColumn('binary_col', 'BINARY(16)')

// JSON type (MySQL 5.7+)
->addColumn('json_col', 'JSON')
```

### Column Constraints

```php
// NOT NULL
->addColumn('required_field', 'VARCHAR(255)', ['NOT NULL'])

// DEFAULT value
->addColumn('status', 'VARCHAR(20)', ['DEFAULT "active"'])

// AUTO_INCREMENT
->addColumn('id', 'INT', ['AUTO_INCREMENT'])

// UNSIGNED
->addColumn('age', 'INT', ['UNSIGNED'])

// COMMENT
->addColumn('description', 'TEXT', ['COMMENT "User description"'])

// Multiple constraints
->addColumn('email', 'VARCHAR(255)', ['NOT NULL', 'UNIQUE'])
```

### Primary Keys

```php
// Single column primary key
->primaryKey('id')

// Composite primary key
->primaryKey(['user_id', 'role_id'])
```

### Foreign Keys

```php
// Basic foreign key
->foreignKey('user_id', 'users', 'id')

// Foreign key with actions
->foreignKey('user_id', 'users', 'id', [
    'on_delete' => 'CASCADE',
    'on_update' => 'CASCADE'
])

// Multiple foreign keys
->foreignKey('user_id', 'users', 'id', ['on_delete' => 'CASCADE'])
->foreignKey('category_id', 'categories', 'id', ['on_delete' => 'SET NULL'])
```

### Indexes

```php
// Single column index
->index('email')

// Multi-column index
->index(['last_name', 'first_name'])

// Named index
->index(['status', 'created_at'], 'idx_status_created')

// Full-text index (MySQL)
->index('description', 'ft_description') // Add FULLTEXT separately if needed
```

### Unique Constraints

```php
// Single column unique
->unique('email')

// Multi-column unique
->unique(['user_id', 'product_id'])
```

### Table Options

```php
->options([
    'ENGINE' => 'InnoDB',           // Storage engine
    'CHARSET' => 'utf8mb4',         // Character set
    'COLLATE' => 'utf8mb4_unicode_ci', // Collation
    'AUTO_INCREMENT' => '1000',     // Starting auto-increment value
    'COMMENT' => '"User accounts table"' // Table comment
])
```

### Examples by Use Case

#### User Authentication Table

```php
$ddl->createTable('users')
    ->ifNotExists()
    ->addColumn('id', 'BIGINT', ['UNSIGNED', 'AUTO_INCREMENT'])
    ->addColumn('email', 'VARCHAR(255)', ['NOT NULL'])
    ->addColumn('password_hash', 'VARCHAR(255)', ['NOT NULL'])
    ->addColumn('email_verified_at', 'TIMESTAMP', ['NULL'])
    ->addColumn('remember_token', 'VARCHAR(100)', ['NULL'])
    ->addColumn('created_at', 'TIMESTAMP', ['DEFAULT CURRENT_TIMESTAMP'])
    ->addColumn('updated_at', 'TIMESTAMP', ['DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'])
    ->primaryKey('id')
    ->unique('email')
    ->index('email_verified_at')
    ->options(['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4'])
    ->execute();
```

#### E-commerce Orders Table

```php
$ddl->createTable('orders')
    ->ifNotExists()
    ->addColumn('id', 'BIGINT', ['UNSIGNED', 'AUTO_INCREMENT'])
    ->addColumn('user_id', 'BIGINT', ['UNSIGNED', 'NOT NULL'])
    ->addColumn('status', 'ENUM("pending", "processing", "completed", "cancelled")', ['DEFAULT "pending"'])
    ->addColumn('total', 'DECIMAL(10,2)', ['NOT NULL'])
    ->addColumn('currency', 'CHAR(3)', ['DEFAULT "USD"'])
    ->addColumn('shipping_address', 'TEXT')
    ->addColumn('billing_address', 'TEXT')
    ->addColumn('notes', 'TEXT', ['NULL'])
    ->addColumn('created_at', 'TIMESTAMP', ['DEFAULT CURRENT_TIMESTAMP'])
    ->addColumn('updated_at', 'TIMESTAMP', ['DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'])
    ->primaryKey('id')
    ->foreignKey('user_id', 'users', 'id', ['on_delete' => 'CASCADE'])
    ->index(['user_id', 'status'])
    ->index('created_at')
    ->options(['ENGINE' => 'InnoDB'])
    ->execute();
```

## ALTER TABLE

### Add Columns

```php
// Add single column
$ddl->alterTable('users')
    ->addColumn('phone', 'VARCHAR(20)')
    ->execute();

// Add multiple columns
$ddl->alterTable('users')
    ->addColumn('phone', 'VARCHAR(20)')
    ->addColumn('address', 'TEXT')
    ->execute();

// Add column with constraints
$ddl->alterTable('users')
    ->addColumn('verified', 'BOOLEAN', ['DEFAULT 0', 'NOT NULL'])
    ->execute();
```

### Modify Columns

```php
// Change column type
$ddl->alterTable('users')
    ->modifyColumn('name', 'VARCHAR(512)', ['NOT NULL'])
    ->execute();

// Change multiple columns
$ddl->alterTable('users')
    ->modifyColumn('first_name', 'VARCHAR(100)')
    ->modifyColumn('last_name', 'VARCHAR(100)')
    ->execute();
```

### Drop Columns

```php
// Drop single column
$ddl->alterTable('users')
    ->dropColumn('old_field')
    ->execute();

// Drop multiple columns
$ddl->alterTable('users')
    ->dropColumn('field1')
    ->dropColumn('field2')
    ->execute();
```

### Rename Columns

```php
$ddl->alterTable('users')
    ->renameColumn('old_name', 'new_name')
    ->execute();
```

### Add Constraints

```php
// Add primary key
$ddl->alterTable('users')
    ->addConstraint('PRIMARY KEY', 'id')
    ->execute();

// Add foreign key
$ddl->alterTable('orders')
    ->addConstraint('FOREIGN KEY', 'user_id', [
        'REFERENCES' => 'users(id)',
        'ON DELETE' => 'CASCADE'
    ])
    ->execute();

// Add unique constraint
$ddl->alterTable('users')
    ->addConstraint('UNIQUE', 'email')
    ->execute();
```

### Drop Constraints

```php
$ddl->alterTable('users')
    ->dropConstraint('fk_user_role')
    ->execute();
```

### Rename Table

```php
$ddl->alterTable('old_users')
    ->renameTo('new_users')
    ->execute();
```

### Complex Alterations

```php
$ddl->alterTable('users')
    ->addColumn('nickname', 'VARCHAR(50)')
    ->modifyColumn('email', 'VARCHAR(320)', ['NOT NULL'])
    ->dropColumn('deprecated_field')
    ->renameColumn('old_status', 'new_status')
    ->execute();
```

## DROP TABLE

### Basic DROP

```php
$ddl->dropTable('temp_table')
    ->execute();
```

### DROP IF EXISTS

```php
$ddl->dropTable('users')
    ->ifExists()
    ->execute();
```

### DROP with CASCADE

```php
// Drop table and all dependent objects
$ddl->dropTable('users')
    ->cascade()
    ->execute();
```

### DROP with RESTRICT

```php
// Drop only if no dependencies
$ddl->dropTable('users')
    ->restrict()
    ->execute();
```

## TRUNCATE TABLE

### Basic TRUNCATE

```php
// Remove all rows from table
$ddl->truncateTable('logs')
    ->execute();
```

### Use Cases for TRUNCATE

TRUNCATE is faster than DELETE and resets AUTO_INCREMENT:

```php
// Clear session data
$ddl->truncateTable('sessions')->execute();

// Clear logs
$ddl->truncateTable('audit_logs')->execute();

// Reset test data
$ddl->truncateTable('test_users')->execute();
```

## Best Practices

### 1. Schema Versioning

Always use migrations or version control for schema changes:

```php
// Migration: 2024_01_01_create_users_table.php
$ddl->createTable('users')
    ->ifNotExists()
    // ... table definition
    ->execute();
```

### 2. Use IF NOT EXISTS / IF EXISTS

Prevent errors in deployment:

```php
$ddl->createTable('users')->ifNotExists()->/* ... */->execute();
$ddl->dropTable('temp')->ifExists()->execute();
```

### 3. Add Indexes Thoughtfully

```php
// Good: Index columns used in WHERE, JOIN, ORDER BY
->index(['user_id', 'created_at'])

// Good: Composite indexes for multi-column queries
->index(['status', 'priority', 'created_at'])

// Avoid: Too many indexes (slows down writes)
```

### 4. Use Appropriate Data Types

```php
// Use specific types for better performance
->addColumn('email', 'VARCHAR(255)')      // Not TEXT
->addColumn('age', 'TINYINT UNSIGNED')    // Not INT
->addColumn('price', 'DECIMAL(10,2)')     // Not FLOAT for money
->addColumn('is_active', 'BOOLEAN')       // Not VARCHAR
```

### 5. Foreign Key Constraints

```php
// Always specify ON DELETE and ON UPDATE
->foreignKey('user_id', 'users', 'id', [
    'on_delete' => 'CASCADE',    // or SET NULL, RESTRICT
    'on_update' => 'CASCADE'
])
```

### 6. Character Sets

```php
// Use utf8mb4 for full Unicode support (including emojis)
->options([
    'CHARSET' => 'utf8mb4',
    'COLLATE' => 'utf8mb4_unicode_ci'
])
```

### 7. Timestamps

```php
// Standard timestamp columns
->addColumn('created_at', 'TIMESTAMP', ['DEFAULT CURRENT_TIMESTAMP'])
->addColumn('updated_at', 'TIMESTAMP', ['DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'])
```

### 8. Testing Schema Changes

Always test DDL operations:

```php
try {
    $ddl->alterTable('users')
        ->addColumn('new_field', 'VARCHAR(255)')
        ->execute();
} catch (\Exception $e) {
    // Rollback or handle error
    error_log("Schema change failed: " . $e->getMessage());
}
```

## Error Handling

```php
use Concept\DBAL\Exception\DBALException;

try {
    $ddl->createTable('users')
        ->addColumn('id', 'INT')
        ->primaryKey('id')
        ->execute();
} catch (DBALException $e) {
    // Handle schema error
    if (str_contains($e->getMessage(), 'already exists')) {
        // Table already exists
    } else {
        throw $e;
    }
}
```

## Performance Considerations

1. **CREATE INDEX separately**: For large tables, create indexes after data insertion
2. **Use TRUNCATE**: Faster than DELETE for clearing tables
3. **Batch ALTER operations**: Combine multiple alterations in one statement
4. **Consider table size**: Large ALTER operations may lock the table

```php
// Good: Single ALTER with multiple changes
$ddl->alterTable('users')
    ->addColumn('field1', 'VARCHAR(50)')
    ->addColumn('field2', 'VARCHAR(50)')
    ->execute();

// Avoid: Multiple ALTER statements
$ddl->alterTable('users')->addColumn('field1', 'VARCHAR(50)')->execute();
$ddl->alterTable('users')->addColumn('field2', 'VARCHAR(50)')->execute();
```
