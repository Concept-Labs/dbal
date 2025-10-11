# SqlExpression Documentation

## Overview

`SqlExpression` is the core expression building system in the DBAL package. It provides a fluent, type-safe interface for constructing SQL expressions while supporting multiple database dialects.

## Architecture

SqlExpression extends the base `Expression` class from the [concept-labs/expression](https://github.com/Concept-Labs/expression) library, adding SQL-specific functionality including:

- SQL keyword management
- Identifier quoting
- Value quoting and type handling
- Operator validation
- Condition building
- Aggregate functions
- **SQL Dialect Support** (MySQL, PostgreSQL, SQLite)

## Namespace

`Concept\DBAL\Expression`

Previously located at `Concept\DBAL\DML\Expression`, it has been moved to a higher level to be shared between DML and DDL components.

## Core Concepts

### 1. Expression Types

SqlExpression categorizes parts of SQL into types:

```php
SqlExpressionInterface::TYPE_KEYWORD    // SQL keywords (SELECT, WHERE, etc.)
SqlExpressionInterface::TYPE_IDENTIFIER // Table/column names
SqlExpressionInterface::TYPE_VALUE      // Values (strings, numbers)
SqlExpressionInterface::TYPE_OPERATOR   // Comparison operators (=, <, >, etc.)
SqlExpressionInterface::TYPE_CONDITION  // Complete conditions
SqlExpressionInterface::TYPE_ALIAS      // Aliases (AS clauses)
```

### 2. Expression Building

Expressions are built using a fluent interface:

```php
$expr = $dml->expression();

// Build a condition
$condition = $expr->condition('age', '>', 18);
// Result: `age` > 18

// Build with keywords
$keyword = $expr->keyword('SELECT');
// Result: SELECT

// Build an identifier
$identifier = $expr->identifier('users.email');
// Result: `users`.`email` (MySQL) or "users"."email" (PostgreSQL)
```

### 3. SQL Dialects

The expression system supports multiple SQL dialects through the `SqlDialectInterface`:

**Supported Dialects:**
- **MySqlDialect** - MySQL/MariaDB
- **PostgreSqlDialect** - PostgreSQL
- **SqliteDialect** - SQLite

Each dialect handles:
- Identifier quoting (backticks for MySQL, double quotes for PostgreSQL/SQLite)
- Value quoting and escaping
- Boolean value representation
- LIMIT/OFFSET syntax
- Feature availability

## SQL Dialect System

### Setting a Dialect

```php
use Concept\DBAL\Expression\Dialect\MySqlDialect;
use Concept\DBAL\Expression\Dialect\PostgreSqlDialect;
use Concept\DBAL\Expression\Dialect\SqliteDialect;

// Set MySQL dialect
$expr = $dml->expression();
$expr->setDialect(new MySqlDialect());

// Set PostgreSQL dialect
$expr->setDialect(new PostgreSqlDialect());

// Set SQLite dialect
$expr->setDialect(new SqliteDialect());
```

### Dialect Differences

#### MySQL
```php
$dialect = new MySqlDialect();

// Identifier quoting
$dialect->quoteIdentifier('users');     // `users`
$dialect->quoteIdentifier('db.users');  // `db`.`users`

// Boolean values
$dialect->quoteValue(true);   // '1'
$dialect->quoteValue(false);  // '0'

// LIMIT syntax
$dialect->getLimitClause(10, 20);  // LIMIT 20, 10

// Supported features
$dialect->supportsFeature('auto_increment');        // true
$dialect->supportsFeature('on_duplicate_key_update'); // true
```

#### PostgreSQL
```php
$dialect = new PostgreSqlDialect();

// Identifier quoting
$dialect->quoteIdentifier('users');     // "users"
$dialect->quoteIdentifier('schema.users'); // "schema"."users"

// Boolean values
$dialect->quoteValue(true);   // 'TRUE'
$dialect->quoteValue(false);  // 'FALSE'

// LIMIT syntax
$dialect->getLimitClause(10, 20);  // LIMIT 10 OFFSET 20

// Supported features
$dialect->supportsFeature('serial');           // true
$dialect->supportsFeature('returning');        // true
$dialect->supportsFeature('jsonb');           // true
```

#### SQLite
```php
$dialect = new SqliteDialect();

// Identifier quoting
$dialect->quoteIdentifier('users');  // "users"

// Boolean values
$dialect->quoteValue(true);   // '1'
$dialect->quoteValue(false);  // '0'

// LIMIT syntax
$dialect->getLimitClause(10, 20);  // LIMIT 10 OFFSET 20

// Supported features
$dialect->supportsFeature('autoincrement');  // true
$dialect->supportsFeature('cte');           // true
```

### Creating Custom Dialects

Extend `AbstractSqlDialect` to create custom dialect support:

```php
namespace App\Database\Dialect;

use Concept\DBAL\Expression\Dialect\AbstractSqlDialect;

class OracleDialect extends AbstractSqlDialect
{
    protected array $supportedFeatures = [
        'sequences',
        'rownum',
        'dual_table',
    ];

    public function getIdentifierQuoteChar(): string
    {
        return '"';
    }

    public function getName(): string
    {
        return 'oracle';
    }

    public function getLimitClause(int $limit, ?int $offset = null): string
    {
        // Oracle uses FETCH FIRST syntax
        if ($offset !== null) {
            return "OFFSET {$offset} ROWS FETCH FIRST {$limit} ROWS ONLY";
        }
        return "FETCH FIRST {$limit} ROWS ONLY";
    }
}
```

## Core Methods

### Keyword Creation

```php
// Create SQL keyword
$expr->keyword('SELECT');  // SELECT
$expr->keyword('WHERE');   // WHERE
$expr->keyword('JOIN');    // JOIN
```

### Identifier Quoting

```php
// Simple identifier
$expr->identifier('users');  // `users` (MySQL) or "users" (PostgreSQL)

// Qualified identifier
$expr->identifier('users.email');  // `users`.`email` or "users"."email"

// Already quoted
$expr->identifier('`users`');  // `users` (unchanged)
```

### Value Quoting

```php
// String value
$expr->value('John Doe');  // 'John Doe'

// Numeric value
$expr->value(42);  // 42

// NULL value
$expr->value(null);  // NULL

// Boolean (dialect-dependent)
$expr->value(true);   // '1' (MySQL/SQLite) or 'TRUE' (PostgreSQL)
$expr->value(false);  // '0' (MySQL/SQLite) or 'FALSE' (PostgreSQL)
```

### Operators

```php
$expr->operator('=');    // =
$expr->operator('>');    // >
$expr->operator('<=');   // <=
$expr->operator('LIKE'); // LIKE
$expr->operator('IN');   // IN
```

### Conditions

```php
// Simple condition
$expr->condition('age', '>', 18);
// Result: `age` > 18

// With NULL
$expr->condition('deleted_at', 'IS', null);
// Result: `deleted_at` IS NULL

// IN condition
$expr->condition('status', 'IN', ['active', 'pending']);
// Result: `status` IN ('active', 'pending')

// With expression
$subquery = $dml->select('id')->from('premium_users');
$expr->condition('user_id', 'IN', $subquery);
// Result: `user_id` IN (SELECT `id` FROM `premium_users`)
```

### Specialized Conditions

```php
// IN condition
$expr->in('status', ['active', 'pending', 'approved']);
// Result: `status` IN ('active', 'pending', 'approved')

// LIKE condition
$expr->like('email', '%@example.com');
// Result: `email` LIKE '%@example.com'
```

### Aliases

```php
// Column alias
$expr->alias('full_name', 'CONCAT(first_name, " ", last_name)');
// Result: CONCAT(first_name, " ", last_name) AS `full_name`

// With expression
$expr->alias('total', $expr->fn('SUM', 'amount'));
// Result: SUM(`amount`) AS `total`
```

### CASE Expressions

```php
$expr->case(
    $expr->condition('age', '<', 18),
    'Minor',
    'Adult'
);
// Result: CASE WHEN (`age` < 18) THEN 'Minor' ELSE 'Adult' END
```

### Aggregate Functions

```php
// Using fn() method
$expr->fn('COUNT', '*');         // COUNT(*)
$expr->fn('SUM', 'amount');      // SUM(`amount`)
$expr->fn('AVG', 'price');       // AVG(`price`)
$expr->fn('MIN', 'created_at');  // MIN(`created_at`)
$expr->fn('MAX', 'updated_at');  // MAX(`updated_at`)

// With expression
$expr->fn('COUNT', $expr->identifier('DISTINCT user_id'));
// COUNT(DISTINCT `user_id`)
```

## Integration with Builders

### DML Builders

DML builders automatically use SqlExpression:

```php
$dml = $dbalManager->dml();

// SELECT with expression
$result = $dml->select('*')
    ->from('users')
    ->where($dml->expression()->condition('age', '>', 18))
    ->execute();

// Using expression for complex WHERE
$expr = $dml->expression();
$complexCondition = $expr->condition('status', '=', 'active')
    ->push($expr->keyword('AND'))
    ->push($expr->condition('verified', '=', true));

$result = $dml->select('*')
    ->from('users')
    ->where($complexCondition)
    ->execute();
```

### DDL Builders

DDL builders also use SqlExpression for proper quoting:

```php
$ddl = $dbalManager->ddl();

// CREATE TABLE uses expressions internally for identifiers
$ddl->createTable('users')
    ->addColumn('id', 'INT', ['AUTO_INCREMENT'])
    ->addColumn('email', 'VARCHAR(255)', ['NOT NULL'])
    ->primaryKey('id')
    ->execute();
```

## Advanced Usage

### Wrapping Expressions

```php
// Wrap with parentheses
$expr->push('SELECT * FROM users')->wrap('(', ')');
// Result: (SELECT * FROM users)

// Wrap with keywords
$expr->push('user_id')->wrap('COUNT(', ')');
// Result: COUNT(user_id)
```

### Joining Expressions

```php
// Join with comma
$expr->push('id', 'name', 'email')->join(', ');
// Result: id, name, email

// Join with AND
$expr->push(
    $expr->condition('status', '=', 'active'),
    $expr->condition('verified', '=', true)
)->join(' AND ');
// Result: `status` = 'active' AND `verified` = 1
```

### Chaining

```php
$expr = $dml->expression()
    ->keyword('SELECT')
    ->push($expr->identifier('*'))
    ->keyword('FROM')
    ->push($expr->identifier('users'))
    ->keyword('WHERE')
    ->push($expr->condition('status', '=', 'active'));
// Result: SELECT * FROM `users` WHERE `status` = 'active'
```

## Expression Factory

The `SqlExpressionFactory` creates SqlExpression instances:

```php
use Concept\DBAL\Expression\SqlExpressionFactory;

$factory = new SqlExpressionFactory($container);
$expr = $factory->create();
```

Configured in `concept.json`:

```json
{
    "Concept\\DBAL\\Expression\\SqlExpressionInterface": {
        "class": "Concept\\DBAL\\Expression\\SqlExpression"
    },
    "Concept\\DBAL\\Expression\\SqlExpressionFactoryInterface": {
        "class": "Concept\\DBAL\\Expression\\SqlExpressionFactory"
    }
}
```

## SqlExpressionAware Trait

Classes can use SqlExpression through the `SqlExpressionAwareTrait`:

```php
use Concept\DBAL\Expression\Contract\SqlExpressionAwareTrait;
use Concept\DBAL\Expression\SqlExpressionInterface;

class MyBuilder
{
    use SqlExpressionAwareTrait;

    public function __construct(SqlExpressionInterface $sqlExpressionPrototype)
    {
        $this->setSqlExpressionPrototype($sqlExpressionPrototype);
    }

    public function buildCondition()
    {
        $expr = $this->expression();
        return $expr->condition('status', '=', 'active');
    }
}
```

## Best Practices

### 1. Use Dialect-Specific Features

```php
$dialect = $expr->getDialect();

if ($dialect->supportsFeature('on_duplicate_key_update')) {
    // Use MySQL-specific ON DUPLICATE KEY UPDATE
} else {
    // Fallback to alternative approach
}
```

### 2. Always Use Expressions for User Input

```php
// Good: Using expression for user input
$userEmail = $_POST['email'];
$expr->condition('email', '=', $userEmail);
// Properly quoted and escaped

// Bad: Direct concatenation
$sql = "WHERE email = '" . $userEmail . "'";
// SQL injection risk!
```

### 3. Reuse Expression Instances

```php
// Create once
$expr = $dml->expression();

// Reuse for multiple conditions
$cond1 = $expr->condition('status', '=', 'active');
$cond2 = $expr->condition('verified', '=', true);
```

### 4. Leverage Prototype Pattern

```php
// Expression prototype is automatically cloned
$expr1 = $dml->expression();
$expr2 = $dml->expression();

// They are different instances
$expr1 !== $expr2; // true
```

## Related Documentation

- **[Concept-Labs Expression Library](https://github.com/Concept-Labs/expression)** - Base expression system
- **[DML Guide](dml-guide.md)** - Data Manipulation Language operations
- **[DDL Guide](ddl-guide.md)** - Data Definition Language operations
- **[Architecture](architecture.md)** - Overall DBAL architecture

## Examples

### Example 1: Complex Condition with Dialect

```php
use Concept\DBAL\Expression\Dialect\MySqlDialect;

$expr = $dml->expression();
$expr->setDialect(new MySqlDialect());

// Build complex condition
$condition = $expr->condition('status', '=', 'active')
    ->push($expr->keyword('AND'))
    ->push(
        $expr->condition('age', '>=', 18)
            ->push($expr->keyword('OR'))
            ->push($expr->condition('parent_approved', '=', true))
            ->wrap('(', ')')
    );

// Use in query
$dml->select('*')
    ->from('users')
    ->where($condition)
    ->execute();
```

### Example 2: Subquery with Expressions

```php
$subquery = $dml->select($expr->fn('MAX', 'salary'))
    ->from('employees')
    ->where($expr->condition('department', '=', 'IT'));

$mainQuery = $dml->select('*')
    ->from('employees')
    ->where($expr->condition('salary', '=', $subquery))
    ->execute();
```

### Example 3: Cross-Dialect Query

```php
function buildQuery($dml, $dialect) {
    $expr = $dml->expression();
    $expr->setDialect($dialect);
    
    $query = $dml->select('*')
        ->from('users')
        ->where($expr->condition('active', '=', true));
    
    if ($dialect->supportsFeature('returning')) {
        // PostgreSQL specific
        $query->returning('id, email');
    }
    
    return $query;
}

// Use with MySQL
$mysqlQuery = buildQuery($dml, new MySqlDialect());

// Use with PostgreSQL
$pgQuery = buildQuery($dml, new PostgreSqlDialect());
```

## Troubleshooting

### Issue: Incorrect Quoting

**Problem:** Identifiers not properly quoted

**Solution:** Ensure dialect is set correctly

```php
$expr->setDialect(new MySqlDialect());  // For MySQL
$expr->setDialect(new PostgreSqlDialect());  // For PostgreSQL
```

### Issue: Unsupported Feature

**Problem:** Using dialect-specific feature not available

**Solution:** Check feature support first

```php
if ($expr->getDialect()->supportsFeature('auto_increment')) {
    // Use AUTO_INCREMENT
} else if ($expr->getDialect()->supportsFeature('serial')) {
    // Use SERIAL (PostgreSQL)
} else {
    // Fallback approach
}
```

### Issue: Expression Type Mismatch

**Problem:** Wrong expression type used

**Solution:** Use appropriate method for the type

```php
// For keywords
$expr->keyword('SELECT');

// For identifiers
$expr->identifier('users');

// For values
$expr->value('john@example.com');
```

## Summary

SqlExpression provides:

✅ **Type-safe** SQL expression building  
✅ **Multi-dialect** support (MySQL, PostgreSQL, SQLite)  
✅ **Proper quoting** and escaping  
✅ **Fluent interface** for easy chaining  
✅ **Extension points** for custom dialects  
✅ **Integration** with DML and DDL builders  
✅ **Base expression** functionality from concept-labs/expression

The expression system is the foundation of DBAL's query building capabilities, ensuring SQL is generated correctly and securely across different database systems.
