# Expression Guide

The expression system provides a powerful way to build complex SQL expressions in a type-safe manner.

## Table of Contents

1. [Basic Expressions](#basic-expressions)
2. [Comparison Operations](#comparison-operations)
3. [Logical Operations](#logical-operations)
4. [Arithmetic Operations](#arithmetic-operations)
5. [Aggregate Functions](#aggregate-functions)
6. [String Functions](#string-functions)
7. [Date Functions](#date-functions)
8. [Advanced Expressions](#advanced-expressions)

## Basic Expressions

### Creating Expressions

```php
$dml = $dbalManager->dml();

// Get expression builder
$expr = $dml->expr();

// Field reference
$field = $expr->field('users.email');

// Literal value
$value = $expr->literal('john@example.com');

// Raw SQL
$raw = $expr->raw('COUNT(*)');
```

### Expression in Queries

```php
// Use expressions in WHERE clauses
$dml->select('*')
    ->from('users')
    ->where($expr->field('age')->greaterThan(18))
    ->execute();
```

## Comparison Operations

### Basic Comparisons

```php
// Equal
$expr->field('status')->equals('active')
$expr->field('status')->eq('active')

// Not Equal
$expr->field('status')->notEquals('deleted')
$expr->field('status')->neq('deleted')

// Greater Than
$expr->field('age')->greaterThan(18)
$expr->field('age')->gt(18)

// Greater Than or Equal
$expr->field('age')->greaterThanOrEqual(18)
$expr->field('age')->gte(18)

// Less Than
$expr->field('price')->lessThan(100)
$expr->field('price')->lt(100)

// Less Than or Equal
$expr->field('price')->lessThanOrEqual(99.99)
$expr->field('price')->lte(99.99)
```

### Range Comparisons

```php
// BETWEEN
$expr->field('created_at')
    ->between('2024-01-01', '2024-12-31')

// NOT BETWEEN
$expr->field('price')
    ->notBetween(10, 20)
```

### Set Operations

```php
// IN
$expr->field('status')
    ->in(['active', 'pending', 'processing'])

// NOT IN
$expr->field('status')
    ->notIn(['deleted', 'banned'])

// IN with subquery
$subquery = $dml->select('id')->from('premium_users');
$expr->field('user_id')->in($subquery)
```

### NULL Checks

```php
// IS NULL
$expr->field('deleted_at')->isNull()

// IS NOT NULL
$expr->field('email_verified_at')->isNotNull()
```

### Pattern Matching

```php
// LIKE
$expr->field('email')->like('%@example.com')

// NOT LIKE
$expr->field('email')->notLike('%spam%')

// Case-insensitive LIKE (MySQL)
$expr->field('name')->ilike('john%')
```

## Logical Operations

### AND / OR

```php
// AND (all conditions must be true)
$expr->and(
    $expr->field('status')->equals('active'),
    $expr->field('age')->greaterThan(18),
    $expr->field('verified')->equals(true)
)

// OR (at least one condition must be true)
$expr->or(
    $expr->field('role')->equals('admin'),
    $expr->field('role')->equals('moderator')
)
```

### NOT

```php
// Negate expression
$expr->not(
    $expr->field('status')->equals('deleted')
)
```

### Complex Logic

```php
// Nested conditions: (status = 'active' AND age > 18) OR role = 'admin'
$expr->or(
    $expr->and(
        $expr->field('status')->equals('active'),
        $expr->field('age')->greaterThan(18)
    ),
    $expr->field('role')->equals('admin')
)
```

## Arithmetic Operations

### Basic Math

```php
// Addition
$expr->field('price')->add(10)
$expr->field('quantity')->plus($expr->field('bonus'))

// Subtraction
$expr->field('price')->subtract(5)
$expr->field('total')->minus($expr->field('discount'))

// Multiplication
$expr->field('price')->multiply(1.1)
$expr->field('quantity')->times($expr->field('unit_price'))

// Division
$expr->field('total')->divide(12)
$expr->field('amount')->dividedBy($expr->field('rate'))

// Modulo
$expr->field('id')->modulo(2)
$expr->field('value')->mod(10)
```

### Complex Calculations

```php
// Calculate discounted price: price * (1 - discount_percent / 100)
$expr->field('price')
    ->multiply(
        $expr->literal(1)->subtract(
            $expr->field('discount_percent')->divide(100)
        )
    )

// Calculate tax: (price + shipping) * tax_rate
$expr->field('price')
    ->add($expr->field('shipping'))
    ->multiply($expr->field('tax_rate'))
```

## Aggregate Functions

### Common Aggregates

```php
// COUNT
$expr->count('*')
$expr->count('DISTINCT user_id')

// SUM
$expr->sum('total')
$expr->sum('DISTINCT amount')

// AVG
$expr->avg('rating')
$expr->average('price')

// MIN
$expr->min('price')

// MAX
$expr->max('created_at')
```

### Using in Queries

```php
// Count orders per user
$dml->select(
    'user_id',
    $expr->count('*')->as('order_count')
)
->from('orders')
->groupBy('user_id')
->execute();

// Calculate statistics
$dml->select(
    $expr->sum('total')->as('total_sales'),
    $expr->avg('total')->as('average_order'),
    $expr->min('total')->as('min_order'),
    $expr->max('total')->as('max_order')
)
->from('orders')
->execute();
```

### Window Functions

```php
// Rank by salary
$expr->rank()->over(
    $expr->orderBy('salary', 'DESC')
)

// Row number partitioned by department
$expr->rowNumber()->over(
    $expr->partitionBy('department_id')
        ->orderBy('salary', 'DESC')
)

// Running total
$expr->sum('amount')->over(
    $expr->partitionBy('user_id')
        ->orderBy('created_at')
)
```

## String Functions

### Basic String Operations

```php
// CONCAT
$expr->concat('first_name', ' ', 'last_name')

// CONCAT_WS (with separator)
$expr->concatWs(' ', 'first_name', 'middle_name', 'last_name')

// SUBSTRING
$expr->substring('email', 1, 10)
$expr->substr('description', 1, 100)

// LENGTH
$expr->length('description')

// UPPER / LOWER
$expr->upper('name')
$expr->lower('email')

// TRIM
$expr->trim('name')
$expr->ltrim('text')  // Left trim
$expr->rtrim('text')  // Right trim
```

### Pattern Operations

```php
// REPLACE
$expr->replace('text', 'old', 'new')

// REGEXP (MySQL)
$expr->regexp('email', '^[a-z]+@[a-z]+\\.[a-z]+$')
```

### Using in Queries

```php
// Search by full name
$dml->select('*')
    ->from('users')
    ->where(
        $expr->concat('first_name', ' ', 'last_name')
            ->like('%John Doe%')
    )
    ->execute();

// Get domain from email
$dml->select(
    'email',
    $expr->substring(
        'email',
        $expr->position('@', 'email')->add(1)
    )->as('domain')
)
->from('users')
->execute();
```

## Date Functions

### Current Date/Time

```php
// NOW()
$expr->now()

// CURRENT_DATE
$expr->currentDate()

// CURRENT_TIME
$expr->currentTime()

// CURRENT_TIMESTAMP
$expr->currentTimestamp()
```

### Date Manipulation

```php
// Add interval
$expr->dateAdd('created_at', 7, 'DAY')
$expr->dateAdd('created_at', 1, 'MONTH')
$expr->dateAdd('created_at', 1, 'YEAR')

// Subtract interval
$expr->dateSub('expires_at', 1, 'HOUR')
$expr->dateSub('created_at', 30, 'DAY')

// Date difference
$expr->dateDiff('end_date', 'start_date')

// Extract parts
$expr->year('created_at')
$expr->month('created_at')
$expr->day('created_at')
$expr->hour('created_at')
$expr->minute('created_at')
$expr->second('created_at')
```

### Date Formatting

```php
// FORMAT
$expr->dateFormat('created_at', '%Y-%m-%d')
$expr->dateFormat('created_at', '%Y-%m-%d %H:%i:%s')

// UNIX_TIMESTAMP
$expr->unixTimestamp('created_at')

// FROM_UNIXTIME
$expr->fromUnixTime(1234567890)
```

### Using in Queries

```php
// Get records from last 30 days
$dml->select('*')
    ->from('orders')
    ->where(
        $expr->field('created_at')
            ->greaterThan(
                $expr->dateSub($expr->now(), 30, 'DAY')
            )
    )
    ->execute();

// Group by month
$dml->select(
    $expr->dateFormat('created_at', '%Y-%m')->as('month'),
    $expr->count('*')->as('total')
)
->from('orders')
->groupBy($expr->dateFormat('created_at', '%Y-%m'))
->execute();
```

## Advanced Expressions

### CASE Statements

```php
// Simple CASE
$expr->case()
    ->when($expr->field('status')->equals('active'), 'Active User')
    ->when($expr->field('status')->equals('inactive'), 'Inactive User')
    ->else('Unknown')
    ->end()

// Searched CASE
$expr->case()
    ->when($expr->field('age')->lessThan(18), 'Minor')
    ->when($expr->field('age')->between(18, 65), 'Adult')
    ->else('Senior')
    ->end()
    ->as('age_group')
```

### COALESCE

```php
// Return first non-NULL value
$expr->coalesce('nickname', 'username', 'email')
```

### NULLIF

```php
// Return NULL if values are equal
$expr->nullif('actual_value', 'default_value')
```

### GREATEST / LEAST

```php
// Get maximum value
$expr->greatest('price1', 'price2', 'price3')

// Get minimum value
$expr->least('price1', 'price2', 'price3')
```

### Subquery Expressions

```php
// EXISTS
$subquery = $dml->select('1')
    ->from('orders')
    ->where('orders.user_id', '=', 'users.id');

$expr->exists($subquery)

// ANY/SOME
$expr->field('price')
    ->greaterThan(
        $expr->any($subquery)
    )

// ALL
$expr->field('price')
    ->greaterThan(
        $expr->all($subquery)
    )
```

### Custom Functions

```php
// Call any SQL function
$expr->function('CUSTOM_FUNC', 'arg1', 'arg2')

// MD5
$expr->md5('password')

// RAND (MySQL)
$expr->rand()

// UUID
$expr->uuid()
```

## Expression Composition

### Chaining

```php
// Complex expression through chaining
$expr->field('price')
    ->multiply(1.1)                    // Apply 10% markup
    ->subtract($expr->field('discount')) // Subtract discount
    ->multiply($expr->field('quantity')) // Multiply by quantity
    ->greaterThan(100)                 // Check if > 100
```

### Aliasing

```php
// Name the expression result
$expr->field('first_name')
    ->concat(' ', $expr->field('last_name'))
    ->as('full_name')
```

### Using with Query Builder

```php
$dml->select(
    'id',
    $expr->case()
        ->when($expr->field('price')->lessThan(50), 'Cheap')
        ->when($expr->field('price')->between(50, 200), 'Medium')
        ->else('Expensive')
        ->end()
        ->as('price_category'),
    $expr->field('price')
        ->multiply(1.2)
        ->as('price_with_tax')
)
->from('products')
->where(
    $expr->field('stock')
        ->greaterThan(0)
        ->and($expr->field('active')->equals(true))
)
->execute();
```

## Best Practices

1. **Use expressions for complex logic**: Keep SQL generation type-safe
2. **Alias complex expressions**: Make results easier to work with
3. **Combine with query builder**: Seamless integration
4. **Test edge cases**: NULL values, division by zero, etc.
5. **Use appropriate functions**: Database-specific functions when needed

## Error Handling

```php
try {
    $result = $dml->select(
        $expr->field('amount')
            ->divide($expr->field('divisor'))
            ->as('result')
    )
    ->from('calculations')
    ->execute();
} catch (\Concept\DBAL\Exception\DBALException $e) {
    // Handle expression errors (e.g., division by zero)
    error_log($e->getMessage());
}
```
