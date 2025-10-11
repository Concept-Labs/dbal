# DDL Builders SqlExpression Integration

## Problem

The DDL builders were manually building SQL strings using string concatenation and `implode()`, instead of using the SqlExpression system like DML builders do. This broke the library's philosophy.

## Solution

Refactored all DDL builders to use SqlExpression via the `getPipeline()` method, following the exact same pattern as DML builders.

## Changes Made

### Before (Manual String Building)

```php
class CreateTableBuilder extends SqlBuilder
{
    protected function buildQuery(): string
    {
        $parts = ['CREATE TABLE'];
        
        if ($this->ifNotExists) {
            $parts[] = 'IF NOT EXISTS';
        }
        
        $parts[] = $this->table;
        
        // Manual column building
        $columnDefs = [];
        foreach ($this->columns as $name => $column) {
            $def = $name . ' ' . $column['type'];
            foreach ($column['options'] as $key => $value) {
                if (is_numeric($key)) {
                    $def .= ' ' . $value;
                } else {
                    $def .= ' ' . $key . ' ' . $value;
                }
            }
            $columnDefs[] = $def;
        }
        
        $parts[] = '(' . implode(', ', $columnDefs) . ')';
        
        return implode(' ', $parts);
    }
}
```

### After (SqlExpression)

```php
class CreateTableBuilder extends SqlBuilder
{
    protected function getPipeline(): SqlExpressionInterface
    {
        $expr = $this->expression();
        
        // CREATE TABLE
        $expr->push($this->expression()->keyword('CREATE'))
            ->push($this->expression()->keyword('TABLE'));
        
        // IF NOT EXISTS
        if ($this->ifNotExists) {
            $expr->push($this->expression()->keyword('IF'))
                ->push($this->expression()->keyword('NOT'))
                ->push($this->expression()->keyword('EXISTS'));
        }
        
        // Table name (properly quoted)
        $expr->push($this->expression()->identifier($this->table));
        
        // Column definitions
        $columnDefs = $this->expression()->join(', ');
        
        foreach ($this->columns as $name => $column) {
            $colDef = $this->expression()
                ->push($this->expression()->identifier($name))
                ->push($column['type']);
            
            foreach ($column['options'] as $key => $value) {
                if (is_numeric($key)) {
                    $colDef->push($value);
                } else {
                    $colDef->push($key)->push($value);
                }
            }
            
            $columnDefs->push($colDef->join(' '));
        }
        
        $expr->push($columnDefs->wrap('(', ')'));
        
        return $expr->join(' ');
    }
}
```

## Benefits

### 1. Proper Identifier Quoting

**Before:**
```php
$parts[] = $this->table;  // No quoting
$def = $name . ' ' . $type;  // No quoting
```

**After:**
```php
$expr->push($this->expression()->identifier($this->table));  // Dialect-aware quoting
$colDef->push($this->expression()->identifier($name));  // Properly quoted
```

### 2. Dialect Support

The expression system automatically uses the configured SQL dialect for quoting:

```php
// MySQL
$expr->identifier('users');  // `users`

// PostgreSQL  
$expr->identifier('users');  // "users"
```

### 3. Keyword Management

**Before:**
```php
$parts[] = 'CREATE TABLE';  // Hard-coded strings
$parts[] = 'IF NOT EXISTS';
```

**After:**
```php
$expr->push($this->expression()->keyword('CREATE'))
    ->push($this->expression()->keyword('TABLE'));
$expr->push($this->expression()->keyword('IF'))
    ->push($this->expression()->keyword('NOT'))
    ->push($this->expression()->keyword('EXISTS'));
```

### 4. Philosophy Alignment

Now both DML and DDL builders:
- ✅ Extend `SqlBuilder`
- ✅ Implement `getPipeline()` method
- ✅ Use `expression()` for building SQL
- ✅ Use `keyword()` for SQL keywords
- ✅ Use `identifier()` for table/column names
- ✅ Use `join()` for combining expressions
- ✅ Support dialect-aware quoting
- ✅ Return `SqlExpressionInterface`

## Updated Builders

### CreateTableBuilder
- Uses `getPipeline()` instead of `buildQuery()`
- Identifiers properly quoted
- Keywords use `keyword()` method
- Expression-based column definitions

### AlterTableBuilder  
- Uses `getPipeline()` instead of `buildQuery()`
- All actions (ADD, MODIFY, DROP, RENAME) use expressions
- Identifiers properly quoted
- Keywords use `keyword()` method

### DropTableBuilder
- Uses `getPipeline()` instead of `buildQuery()`
- IF EXISTS uses keyword expressions
- CASCADE/RESTRICT use keyword expressions
- Table name properly quoted

### TruncateTableBuilder
- Uses `getPipeline()` instead of `buildQuery()`
- Simple expression building
- Table name properly quoted

## Architecture Flow

```
DML Builders                    DDL Builders
     │                               │
     ├─ getPipeline()                ├─ getPipeline()
     │                               │
     ├─ expression()                 ├─ expression()
     │   ├─ keyword()                │   ├─ keyword()
     │   ├─ identifier()             │   ├─ identifier()
     │   └─ join()                   │   └─ join()
     │                               │
     └─ SqlExpression ←──────────────┘
            │
            ├─ Dialect Support
            ├─ Proper Quoting
            └─ Type Safety
```

## Example Outputs

### MySQL Dialect
```sql
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE (`email`)
)
```

### PostgreSQL Dialect
```sql
CREATE TABLE IF NOT EXISTS "users" (
    "id" SERIAL,
    "email" VARCHAR(255) NOT NULL,
    PRIMARY KEY ("id"),
    UNIQUE ("email")
)
```

## Commit

**Hash:** 91c20c4

**Message:**
```
Fix DDL builders to use SqlExpression instead of manual string building

- Updated CreateTableBuilder to use SqlExpression via getPipeline()
- Updated AlterTableBuilder to use SqlExpression via getPipeline()
- Updated DropTableBuilder to use SqlExpression via getPipeline()
- Updated TruncateTableBuilder to use SqlExpression via getPipeline()
- All DDL builders now follow DML philosophy of using expression system
- Properly quote identifiers and use keyword() for SQL keywords
- Replace manual string concatenation with expression building
```

## Verification

All DDL builders now:
- ✅ Use `getPipeline()` method
- ✅ Return `SqlExpressionInterface`
- ✅ Use expression system for SQL building
- ✅ Properly quote identifiers via dialect
- ✅ Follow DML builder philosophy
- ✅ Pass PHP syntax validation

## Conclusion

The DDL builders now fully align with the library's philosophy. They use the SqlExpression system for building SQL, just like DML builders, ensuring:
- Consistent architecture
- Proper identifier quoting
- Dialect support
- Type safety
- Maintainability

No more manual string concatenation in DDL builders!
