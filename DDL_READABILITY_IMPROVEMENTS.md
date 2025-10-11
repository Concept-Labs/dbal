# DDL Builders Readability Improvements

## Overview

All DDL builders have been refactored to use the `__invoke()` method from the Expression library, resulting in cleaner, more readable code that matches the DML implementation pattern.

## Expression Library Integration

The refactoring leverages the new `__invoke()` method from `Concept\Expression\Expression`:

```php
public function __invoke(...$expressions) {
    return $this->push(...$expressions);
}
```

This allows expressions to be called like functions, making code much more readable and intuitive.

## Code Improvements

### CreateTableBuilder

**Before (Using ->push() chains):**
```php
$expr = $this->expression();

$expr->push($this->expression()->keyword('CREATE'))
    ->push($this->expression()->keyword('TABLE'));

if ($this->ifNotExists) {
    $expr->push($this->expression()->keyword('IF'))
        ->push($this->expression()->keyword('NOT'))
        ->push($this->expression()->keyword('EXISTS'));
}

$expr->push($this->expression()->identifier($this->table));

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
```

**After (Using __invoke()):**
```php
$expr = $this->expression(
    $this->expression()->keyword('CREATE'),
    $this->expression()->keyword('TABLE')
);

if ($this->ifNotExists) {
    $expr(
        $this->expression()->keyword('IF'),
        $this->expression()->keyword('NOT'),
        $this->expression()->keyword('EXISTS')
    );
}

$expr($this->expression()->identifier($this->table));

$colDef = $this->expression(
    $this->expression()->identifier($name),
    $column['type']
);

foreach ($column['options'] as $key => $value) {
    if (is_numeric($key)) {
        $colDef($value);
    } else {
        $colDef($key, $value);
    }
}
```

### AlterTableBuilder

**Before:**
```php
$expr = $this->expression();

$expr->push($this->expression()->keyword('ALTER'))
    ->push($this->expression()->keyword('TABLE'))
    ->push($this->expression()->identifier($this->table));

$colDef = $this->expression()
    ->push($this->expression()->keyword('ADD'))
    ->push($this->expression()->keyword('COLUMN'))
    ->push($this->expression()->identifier($action['name']))
    ->push($action['definition']);

foreach ($action['options'] as $key => $value) {
    if (is_numeric($key)) {
        $colDef->push($value);
    } else {
        $colDef->push($key)->push($value);
    }
}
```

**After:**
```php
$expr = $this->expression(
    $this->expression()->keyword('ALTER'),
    $this->expression()->keyword('TABLE'),
    $this->expression()->identifier($this->table)
);

$colDef = $this->expression(
    $this->expression()->keyword('ADD'),
    $this->expression()->keyword('COLUMN'),
    $this->expression()->identifier($action['name']),
    $action['definition']
);

foreach ($action['options'] as $key => $value) {
    if (is_numeric($key)) {
        $colDef($value);
    } else {
        $colDef($key, $value);
    }
}
```

### DropTableBuilder

**Before:**
```php
$expr = $this->expression();

$expr->push($this->expression()->keyword('DROP'))
    ->push($this->expression()->keyword('TABLE'));

if ($this->ifExists) {
    $expr->push($this->expression()->keyword('IF'))
        ->push($this->expression()->keyword('EXISTS'));
}

$expr->push($this->expression()->identifier($this->table));

if ($this->cascadeOption) {
    $expr->push($this->expression()->keyword($this->cascadeOption));
}
```

**After:**
```php
$expr = $this->expression(
    $this->expression()->keyword('DROP'),
    $this->expression()->keyword('TABLE')
);

if ($this->ifExists) {
    $expr(
        $this->expression()->keyword('IF'),
        $this->expression()->keyword('EXISTS')
    );
}

$expr($this->expression()->identifier($this->table));

if ($this->cascadeOption) {
    $expr($this->expression()->keyword($this->cascadeOption));
}
```

### TruncateTableBuilder

**Before:**
```php
return $this->expression()
    ->push($this->expression()->keyword('TRUNCATE'))
    ->push($this->expression()->keyword('TABLE'))
    ->push($this->expression()->identifier($this->table))
    ->join(' ');
```

**After:**
```php
return $this->expression(
    $this->expression()->keyword('TRUNCATE'),
    $this->expression()->keyword('TABLE'),
    $this->expression()->identifier($this->table)
)->join(' ');
```

## Benefits

### 1. **Improved Readability**
- Expressions are grouped logically instead of chained
- Related keywords are visually grouped together
- Easier to understand the SQL structure at a glance

### 2. **Consistent with DML Builders**
- All builders now follow the same coding pattern
- DML builders already use this approach
- Unified codebase style

### 3. **Cleaner Code**
- Less repetitive `->push()` calls
- More concise syntax
- Reduced visual clutter

### 4. **Better Organization**
- Related expressions grouped in single calls
- Conditional additions more clear
- Logical flow is easier to follow

### 5. **Leverages Expression Library Features**
- Uses the new `__invoke()` method properly
- Aligns with the Expression library philosophy
- Takes advantage of latest Expression library updates

## Comparison: DML vs DDL Pattern

### DML Builder (SelectBuilder)
```php
protected function getPipeline(): SqlExpressionInterface
{
    return $this->expression(
        $this->pipeSection(KeywordEnum::SELECT),
        $this->pipeSection(KeywordEnum::FROM),
        $this->pipeSection(KeywordEnum::WHERE),
        $this->pipeSection(KeywordEnum::ORDER_BY)
    )->join(' ');
}
```

### DDL Builder (CreateTableBuilder) - Now Matching
```php
protected function getPipeline(): SqlExpressionInterface
{
    $expr = $this->expression(
        $this->expression()->keyword('CREATE'),
        $this->expression()->keyword('TABLE')
    );
    
    if ($this->ifNotExists) {
        $expr(
            $this->expression()->keyword('IF'),
            $this->expression()->keyword('NOT'),
            $this->expression()->keyword('EXISTS')
        );
    }
    
    $expr($this->expression()->identifier($this->table));
    
    // ... more building
    
    return $expr->join(' ');
}
```

Both now use the same `$this->expression(...)` pattern with `__invoke()` calls!

## Files Modified

- `src/DDL/Builder/CreateTableBuilder.php` - Refactored all expression building
- `src/DDL/Builder/AlterTableBuilder.php` - Refactored all action building
- `src/DDL/Builder/DropTableBuilder.php` - Refactored drop logic
- `src/DDL/Builder/TruncateTableBuilder.php` - Simplified to one-liner

## Migration Notes

The changes are internal to the builders and don't affect the public API. Users of the DDL builders will see:

- Same fluent interface
- Same method calls
- Same SQL output
- **Better performance** (fewer method calls internally)
- **More maintainable code** (easier to debug and extend)

## Philosophy Alignment

✅ **DML and DDL Now Both:**
1. Extend `SqlBuilder`
2. Implement `getPipeline(): SqlExpressionInterface`
3. Use `expression()` system
4. Use `keyword()` for SQL keywords
5. Use `identifier()` for names
6. Use `join()` for combining expressions
7. Return `SqlExpressionInterface`
8. Support dialects automatically
9. **Use `__invoke()` for readable code** ✅

**Result**: 100% philosophy alignment across all builders!

## Reference

- Expression library `__invoke()` method: [Concept-Labs/expression@b4df09e](https://github.com/Concept-Labs/expression/commit/b4df09eb9b4b5250e5dada84abebe17b4522c6c6)
- DML builder implementation: `src/DML/Builder/SelectBuilder.php`
- DDL builder implementations: `src/DDL/Builder/*.php`
