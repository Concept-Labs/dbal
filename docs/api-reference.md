# API Reference

Quick reference guide for Concept DBAL interfaces and methods.

## DmlManagerInterface

Main interface for creating query builders.

### Methods

#### select(...$columns): SelectBuilderInterface
Create a SELECT query builder.

```php
$dml->select('*');
$dml->select('id', 'name', 'email');
$dml->select(['id', 'name']);
```

#### insert(?string $table = null): InsertBuilderInterface
Create an INSERT query builder.

```php
$dml->insert('users');
$dml->insert()->into('users');
```

#### update(string|array $table): UpdateBuilderInterface
Create an UPDATE query builder.

```php
$dml->update('users');
```

#### delete(?string $table = null): DeleteBuilderInterface
Create a DELETE query builder.

```php
$dml->delete('users');
$dml->delete()->from('users');
```

#### expr(): SqlExpressionInterface
Get an expression builder instance.

```php
$expr = $dml->expr();
$expr->condition('age', '>', 18);
```

---

## SelectBuilderInterface

Builder for SELECT queries.

### Basic Methods

#### from(string|array $table, ?string $alias = null): static
Set the FROM clause.

```php
->from('users')
->from('users', 'u')
->from(['u' => 'users'])
```

#### where(SqlExpressionInterface ...$conditions): static
Add WHERE condition (AND).

```php
->where($expr->condition('age', '>', 18))
->where($expr->condition('status', '=', 'active'))
```

#### orWhere(SqlExpressionInterface ...$conditions): static
Add WHERE condition (OR).

```php
->orWhere($expr->condition('role', '=', 'admin'))
```

#### whereIn(string|SqlExpressionInterface $column, array|SqlExpressionInterface $values): static
Add WHERE IN condition.

```php
->whereIn('status', ['active', 'pending'])
```

#### whereLike(string|SqlExpressionInterface $column, string|SqlExpressionInterface $value): static
Add WHERE LIKE condition.

```php
->whereLike('name', 'John%')
```

### JOIN Methods

#### join(string $table, SqlExpressionInterface $condition, ?string $alias = null): static
Add INNER JOIN.

```php
->join('profiles', $expr->condition('users.id', '=', 'profiles.user_id'))
```

#### leftJoin(string $table, SqlExpressionInterface $condition, ?string $alias = null): static
Add LEFT JOIN.

```php
->leftJoin('orders', $expr->condition('users.id', '=', 'orders.user_id'))
```

#### rightJoin(string $table, SqlExpressionInterface $condition, ?string $alias = null): static
Add RIGHT JOIN.

```php
->rightJoin('profiles', $expr->condition('users.id', '=', 'profiles.user_id'))
```

### Grouping & Ordering

#### groupBy(...$columns): static
Add GROUP BY clause.

```php
->groupBy('status')
->groupBy('country', 'city')
```

#### having(SqlExpressionInterface ...$conditions): static
Add HAVING condition.

```php
->having($expr->condition('COUNT(*)', '>', 10))
```

#### orderBy(string $column, string $direction = 'ASC'): static
Add ORDER BY clause.

```php
->orderBy('created_at', 'DESC')
->orderBy('name', 'ASC')
```

### Limits

#### limit(int $limit): static
Set result limit.

```php
->limit(10)
```

#### offset(int $offset): static
Set result offset.

```php
->offset(20)
```

### Other Methods

#### union(SelectBuilderInterface $query): static
Add UNION.

```php
->union($otherQuery)
```

#### unionAll(SelectBuilderInterface $query): static
Add UNION ALL.

```php
->unionAll($otherQuery)
```

#### describe(string $table): static
Get table structure.

```php
->describe('users')
```

---

## InsertBuilderInterface

Builder for INSERT queries.

### Methods

#### insert(?string $table = null): static
Initialize INSERT query.

```php
->insert('users')
```

#### into(string $table): static
Set table for insertion.

```php
->into('users')
```

#### values(array ...$values): static
Set values to insert.

```php
// Single row
->values(['name' => 'John', 'email' => 'john@example.com'])

// Multiple rows
->values(
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com']
)
```

#### ignore(): static
Add IGNORE keyword.

```php
->ignore()
```

---

## UpdateBuilderInterface

Builder for UPDATE queries.

### Methods

#### update(string|array $table): static
Initialize UPDATE query.

```php
->update('users')
```

#### set(string|array $column, mixed $value = null): static
Set column values.

```php
// Single column
->set('status', 'active')

// Multiple columns
->set([
    'status' => 'active',
    'updated_at' => date('Y-m-d H:i:s')
])
```

#### where(SqlExpressionInterface ...$conditions): static
Add WHERE condition.

```php
->where($expr->condition('id', '=', 123))
```

#### join(string $table, SqlExpressionInterface $condition): static
Add JOIN to UPDATE.

```php
->join('profiles', $expr->condition('users.id', '=', 'profiles.user_id'))
```

#### limit(int $limit): static
Limit number of updated rows.

```php
->limit(100)
```

---

## DeleteBuilderInterface

Builder for DELETE queries.

### Methods

#### delete(?string $table = null): static
Initialize DELETE query.

```php
->delete('users')
```

#### from(string $table): static
Set table for deletion.

```php
->from('users')
```

#### where(SqlExpressionInterface ...$conditions): static
Add WHERE condition.

```php
->where($expr->condition('status', '=', 'deleted'))
```

#### limit(int $limit): static
Limit number of deleted rows.

```php
->limit(100)
```

---

## SqlExpressionInterface

Builder for SQL expressions and conditions.

### Condition Methods

#### condition(string|SqlExpressionInterface $left, string $operator, mixed $right): static
Create a condition.

```php
$expr->condition('age', '>', 18)
$expr->condition('status', '=', 'active')
$expr->condition('email', '!=', null)
```

#### in(string|SqlExpressionInterface $column, array|SqlExpressionInterface $values): static
Create IN condition.

```php
$expr->in('status', ['active', 'pending', 'approved'])
```

#### like(string|SqlExpressionInterface $column, string|SqlExpressionInterface $value): static
Create LIKE condition.

```php
$expr->like('name', 'John%')
```

#### case(string|SqlExpressionInterface $condition, string|SqlExpressionInterface $thenValue, string|SqlExpressionInterface|null $elseValue = null): static
Create CASE expression.

```php
$expr->case(
    $expr->condition('age', '>', 18),
    'adult',
    'minor'
)
```

### Aggregate Functions

#### count(string $column, ?string $alias = null): static
COUNT aggregate.

```php
$expr->count('*', 'total')
```

#### sum(string $column, ?string $alias = null): static
SUM aggregate.

```php
$expr->sum('amount', 'total_amount')
```

#### avg(string $column, ?string $alias = null): static
AVG aggregate.

```php
$expr->avg('age', 'average_age')
```

#### max(string $column, ?string $alias = null): static
MAX aggregate.

```php
$expr->max('price', 'highest_price')
```

#### min(string $column, ?string $alias = null): static
MIN aggregate.

```php
$expr->min('price', 'lowest_price')
```

### Component Methods

#### identifier(string $identifier): static
Create identifier (table/column name).

```php
$expr->identifier('users.id')
```

#### value(string $value): static
Create value literal.

```php
$expr->value('John Doe')
```

#### keyword(string $keyword): static
Create SQL keyword.

```php
$expr->keyword('SELECT')
```

#### alias(string $alias, string|SqlExpressionInterface $expression): static
Create alias.

```php
$expr->alias('full_name', 'CONCAT(first_name, " ", last_name)')
```

#### group(...$parts): static
Group expressions with parentheses.

```php
$expr->group(
    $expr->condition('age', '>', 18),
    'AND',
    $expr->condition('status', '=', 'active')
)
```

---

## Common Methods (All Builders)

### execute(): mixed
Execute the query and return results.

```php
$results = $query->execute();
```

### getSql(): string
Get the SQL string without executing.

```php
$sql = $query->getSql();
```

### getParams(): array
Get bound parameters.

```php
$params = $query->getParams();
```

### reset(): static
Reset entire query.

```php
$query->reset();
```

### resetSection(string $section): static
Reset specific query section.

```php
$query->resetSection(KeywordEnum::WHERE);
```

---

## Complete Example

```php
use Concept\DBAL\DML\DmlManagerInterface;

class UserRepository
{
    public function __construct(
        private DmlManagerInterface $dml
    ) {}
    
    public function findActiveAdults(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        return $this->dml
            ->select('id', 'name', 'email', 'age')
            ->from('users')
            ->where($this->dml->expr()->condition('status', '=', 'active'))
            ->where($this->dml->expr()->condition('age', '>=', 18))
            ->where($this->dml->expr()->condition('deleted_at', 'IS', null))
            ->orderBy('created_at', 'DESC')
            ->limit($perPage)
            ->offset($offset)
            ->execute();
    }
}
```

---

## Next Steps

- **[Query Builders Guide](builders.md)** - Detailed builder documentation
- **[SQL Expressions Guide](expressions.md)** - Expression system guide  
- **[Examples](examples.md)** - Real-world usage examples
- **[Best Practices](best-practices.md)** - Recommended patterns
