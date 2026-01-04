# Comparison with Other Solutions

This guide compares Concept DBAL with other popular database abstraction layers and query builders for PHP.

## Overview

| Feature | Concept DBAL | Doctrine DBAL | Laravel Query Builder | PDO | Medoo |
|---------|-------------|---------------|---------------------|-----|-------|
| **Type** | Query Builder | DBAL + Query Builder | Query Builder | Database API | Query Builder |
| **PHP Version** | 8.2+ | 7.4+ | 8.0+ | 5.0+ | 7.3+ |
| **Architecture** | Interface-driven | Object-oriented | Facade + Builder | Procedural/OOP | Simple OOP |
| **DI Support** | Native (Singularity) | Partial | Native (Laravel) | None | None |
| **Query Builder** | ✅ Fluent | ✅ Fluent | ✅ Fluent | ❌ Manual | ✅ Simple |
| **Schema Builder** | 🚧 Planned | ✅ Comprehensive | ✅ Full | ❌ None | ❌ None |
| **Migrations** | ❌ External | ✅ Built-in | ✅ Built-in | ❌ None | ❌ None |
| **ORM** | ❌ No | ❌ No | ✅ Eloquent | ❌ No | ❌ No |
| **Learning Curve** | Low-Medium | Medium-High | Low-Medium | Low | Very Low |
| **Performance** | High | Medium | Medium-High | Highest | High |
| **Flexibility** | Very High | Very High | High | Highest | Medium |
| **Type Safety** | Strong (8.2+) | Good | Good | Weak | Weak |
| **Footprint** | Lightweight | Heavy | Medium | Minimal | Minimal |
| **Standalone** | ✅ Yes | ✅ Yes | ❌ Laravel-only | ✅ Yes | ✅ Yes |

## Detailed Comparisons

### 1. Concept DBAL vs Doctrine DBAL

#### Concept DBAL

**Strengths:**
- 🎯 **Modern PHP** - Built for PHP 8.2+, leveraging latest features
- 🏗️ **Clean Architecture** - Interface-driven design throughout
- 💉 **DI-First** - Native integration with Singularity container
- 🚀 **Lightweight** - Focused on query building without ORM overhead
- 📦 **Modular** - Part of larger Concept ecosystem
- ⚡ **Performance** - Prototype pattern for efficient builder creation
- 🔧 **Type Safety** - Strong typing with PHP 8.2+ features

**Use Cases:**
- Modern PHP applications (8.2+)
- Projects using Concept ecosystem
- Performance-critical query building
- Clean architecture applications
- Microservices requiring lightweight DB layer

**Example:**
```php
$users = $dml->select('id', 'name', 'email')
    ->from('users')
    ->where($dml->expr()->condition('status', '=', 'active'))
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->execute();
```

#### Doctrine DBAL

**Strengths:**
- 📚 **Mature** - Battle-tested in production for years
- 🛠️ **Comprehensive** - Schema management, migrations, events
- 🔌 **Database Support** - Wide range of database platforms
- 🏢 **Enterprise** - Large community and ecosystem
- 📖 **Documentation** - Extensive documentation and examples

**Weaknesses:**
- 📦 **Heavy** - Large footprint with many dependencies
- 🐌 **Performance** - More overhead than lightweight alternatives
- 🎓 **Complexity** - Steep learning curve for advanced features
- 📅 **Legacy Support** - Must support older PHP versions

**Use Cases:**
- Enterprise applications
- Symfony framework projects
- Need for comprehensive schema management
- Multiple database platform support
- Projects requiring mature, proven solution

**Example:**
```php
$queryBuilder = $connection->createQueryBuilder();
$users = $queryBuilder
    ->select('id', 'name', 'email')
    ->from('users')
    ->where('status = :status')
    ->setParameter('status', 'active')
    ->orderBy('created_at', 'DESC')
    ->setMaxResults(10)
    ->fetchAllAssociative();
```

**When to Choose Concept DBAL:**
- ✅ Using PHP 8.2+
- ✅ Want lightweight, focused solution
- ✅ Using Concept ecosystem
- ✅ Value clean architecture and DI
- ✅ Don't need schema management yet

**When to Choose Doctrine DBAL:**
- ✅ Need comprehensive schema tools
- ✅ Using Symfony framework
- ✅ Require multi-database support
- ✅ Want extensive documentation
- ✅ Need proven enterprise solution

---

### 2. Concept DBAL vs Laravel Query Builder

#### Laravel Query Builder

**Strengths:**
- 🎨 **Elegant API** - Beautiful, expressive syntax
- 🏢 **Integrated** - Part of Laravel framework
- 📚 **Rich Features** - Pagination, chunking, aggregates
- 🔄 **Active Development** - Regular updates and improvements
- 👥 **Large Community** - Huge ecosystem and support

**Weaknesses:**
- 🔒 **Framework Lock-in** - Tied to Laravel framework
- 📦 **Dependencies** - Requires Laravel components
- 🎯 **Framework-Specific** - Not ideal for standalone use

**Example:**
```php
$users = DB::table('users')
    ->select('id', 'name', 'email')
    ->where('status', 'active')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();
```

**When to Choose Concept DBAL:**
- ✅ Not using Laravel framework
- ✅ Want standalone library
- ✅ Using Concept ecosystem
- ✅ Need interface-driven design
- ✅ Want more control over DI

**When to Choose Laravel Query Builder:**
- ✅ Using Laravel framework
- ✅ Want integrated solution
- ✅ Need Eloquent ORM integration
- ✅ Value Laravel conventions
- ✅ Want built-in pagination

---

### 3. Concept DBAL vs PDO

#### PDO (PHP Data Objects)

**Strengths:**
- ⚡ **Performance** - Raw, minimal overhead
- 🎯 **Control** - Complete control over SQL
- 📦 **Built-in** - No external dependencies
- 🔧 **Flexibility** - Use any SQL feature

**Weaknesses:**
- 📝 **Manual** - No query builder
- 🐛 **Error-Prone** - Easy to make SQL mistakes
- 🔄 **Repetitive** - Lots of boilerplate code
- 🚫 **No Abstraction** - Platform-specific SQL

**Example:**
```php
$stmt = $pdo->prepare(
    'SELECT id, name, email FROM users 
     WHERE status = :status 
     ORDER BY created_at DESC 
     LIMIT 10'
);
$stmt->execute(['status' => 'active']);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

**When to Choose Concept DBAL:**
- ✅ Want query builder convenience
- ✅ Need type safety
- ✅ Want cleaner, more maintainable code
- ✅ Value fluent interface
- ✅ Need DI integration

**When to Choose PDO:**
- ✅ Maximum performance critical
- ✅ Simple queries only
- ✅ Want zero dependencies
- ✅ Full SQL control required
- ✅ Working with legacy code

---

### 4. Concept DBAL vs Medoo

#### Medoo

**Strengths:**
- 🎯 **Simple** - Easy to learn and use
- 📦 **Lightweight** - Single file library
- 🚀 **Fast** - Minimal overhead
- 📝 **Clean Syntax** - Array-based configuration

**Weaknesses:**
- 🎓 **Limited Features** - Basic functionality only
- 🔧 **Less Flexible** - Array syntax can be limiting
- 📚 **Less Documentation** - Smaller community
- 🎯 **Not Interface-Driven** - Harder to test and extend

**Example:**
```php
$users = $database->select('users', 
    ['id', 'name', 'email'],
    [
        'status' => 'active',
        'ORDER' => ['created_at' => 'DESC'],
        'LIMIT' => 10
    ]
);
```

**When to Choose Concept DBAL:**
- ✅ Need complex queries
- ✅ Want fluent interface
- ✅ Value type safety
- ✅ Need DI support
- ✅ Want interface-driven design

**When to Choose Medoo:**
- ✅ Very simple queries
- ✅ Want minimal footprint
- ✅ Prefer array syntax
- ✅ Quick prototyping
- ✅ Small projects

---

## Feature-by-Feature Comparison

### Query Building

| Feature | Concept DBAL | Doctrine DBAL | Laravel | PDO | Medoo |
|---------|-------------|---------------|---------|-----|-------|
| **SELECT** | ✅ Fluent | ✅ Fluent | ✅ Fluent | ❌ Manual | ✅ Array |
| **INSERT** | ✅ Fluent | ✅ Fluent | ✅ Fluent | ❌ Manual | ✅ Array |
| **UPDATE** | ✅ Fluent | ✅ Fluent | ✅ Fluent | ❌ Manual | ✅ Array |
| **DELETE** | ✅ Fluent | ✅ Fluent | ✅ Fluent | ❌ Manual | ✅ Array |
| **JOINs** | ✅ Full | ✅ Full | ✅ Full | ❌ Manual | ✅ Limited |
| **Subqueries** | ✅ Yes | ✅ Yes | ✅ Yes | ❌ Manual | ⚠️ Limited |
| **Unions** | ✅ Yes | ✅ Yes | ✅ Yes | ❌ Manual | ❌ No |
| **CTEs (WITH)** | ✅ Yes | ✅ Yes | ✅ Yes | ❌ Manual | ❌ No |
| **Raw SQL** | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Native | ✅ Yes |
| **Builder as Parameter** | ✅ Native | ⚠️ Limited | ✅ Native | ❌ No | ❌ No |
| **Derived Tables** | ✅ Yes | ⚠️ Complex | ✅ Yes | ❌ Manual | ❌ No |
| **Scalar Subqueries** | ✅ Yes | ✅ Yes | ✅ Yes | ❌ Manual | ❌ No |

### Builder Object Patterns

Concept DBAL's unique feature is the ability to pass builder objects as parameters throughout the API:

```php
// Concept DBAL - Builders as Parameters
$subquery = $dml->select('user_id')
    ->from('orders')
    ->where($dml->expr()->condition('total', '>', 1000));

// Use builder directly in whereIn
$users = $dml->select('*')
    ->from('users')
    ->whereIn('id', $subquery)
    ->execute();

// Use builder in FROM (derived table)
$result = $dml->select('*')
    ->from(['orders_sub' => $subquery])
    ->execute();

// Use builder in JOIN
$users = $dml->select('*')
    ->from('users')
    ->join(['o' => $subquery], 'o', $condition)
    ->execute();
```

**Doctrine DBAL** requires more verbose syntax:

```php
// Doctrine DBAL - Subqueries
$subQuery = $connection->createQueryBuilder()
    ->select('user_id')
    ->from('orders')
    ->where('total > 1000');

$qb = $connection->createQueryBuilder();
$qb->select('*')
    ->from('users', 'u')
    ->where(
        $qb->expr()->in('u.id', 
            '(' . $subQuery->getSQL() . ')'  // Must convert to SQL string
        )
    )
    ->setParameters($subQuery->getParameters());  // Manual parameter merging
```

**Laravel** supports closures for subqueries:

```php
// Laravel - Subqueries with Closures
$users = DB::table('users')
    ->whereIn('id', function($query) {
        $query->select('user_id')
              ->from('orders')
              ->where('total', '>', 1000);
    })
    ->get();

// Or with query builder objects
$subquery = DB::table('orders')
    ->select('user_id')
    ->where('total', '>', 1000);

$users = DB::table('users')
    ->whereInSub('id', $subquery)
    ->get();
```

### Alias Support Comparison

**Concept DBAL** provides consistent alias syntax across all methods:

```php
// Aliases with array syntax - works everywhere
$orderStats = $dml->select('user_id', ['total' => 'SUM(amount)'])
    ->from('orders')
    ->groupBy('user_id');

$results = $dml->select(['name' => 'u.name'], ['total' => 'os.total'])
    ->from(['u' => 'users'])
    ->join(['os' => $orderStats], 'os', $condition)  // Builder with alias
    ->execute();
```

**Doctrine DBAL**:

```php
// Aliases require different syntax
$qb = $connection->createQueryBuilder();
$qb->select('u.name', 'os.total')
    ->from('users', 'u')
    ->leftJoin('u', '(' . $subQuery->getSQL() . ')', 'os', 'u.id = os.user_id');
// Derived table joins are complex
```

**Laravel**:

```php
// Aliases with as() or array syntax
$results = DB::table('users as u')
    ->select('u.name as name', 'os.total as total')
    ->joinSub($orderStats, 'os', function($join) {
        $join->on('u.id', '=', 'os.user_id');
    })
    ->get();
```

### Common Table Expressions (CTEs)

**Concept DBAL**:

```php
// Clean CTE syntax with builder objects
$activeUsers = $dml->select('id', 'name')
    ->from('users')
    ->where($dml->expr()->condition('status', '=', 'active'));

$results = $dml->select('*')
    ->with('active_users', $activeUsers)  // Pass builder directly
    ->from('active_users')
    ->execute();
```

**Doctrine DBAL**:

```php
// Manual CTE construction (complex)
$qb = $connection->createQueryBuilder();
$sql = 'WITH active_users AS (' . $subQuery->getSQL() . ') '
     . 'SELECT * FROM active_users';
$stmt = $connection->executeQuery($sql, $subQuery->getParameters());
```

**Laravel**:

```php
// CTE support via withExpression (Laravel 8.5+)
$activeUsers = DB::table('users')
    ->select('id', 'name')
    ->where('status', 'active');

$results = DB::table('active_users')
    ->withExpression('active_users', $activeUsers)
    ->get();
```

### Type Safety & Features

| Feature | Concept DBAL | Doctrine DBAL | Laravel | PDO | Medoo |
|---------|-------------|---------------|---------|-----|-------|
| **PHP 8.2 Types** | ✅ Full | ⚠️ Partial | ⚠️ Partial | ❌ No | ❌ No |
| **Named Params** | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes |
| **Type Hints** | ✅ Strong | ⚠️ Good | ⚠️ Good | ❌ Weak | ❌ Weak |
| **IDE Support** | ✅ Excellent | ✅ Good | ✅ Excellent | ⚠️ Basic | ⚠️ Basic |
| **Static Analysis** | ✅ Full | ⚠️ Partial | ⚠️ Partial | ❌ Limited | ❌ Limited |

### Architecture & Design

| Feature | Concept DBAL | Doctrine DBAL | Laravel | PDO | Medoo |
|---------|-------------|---------------|---------|-----|-------|
| **Interfaces** | ✅ All | ⚠️ Partial | ⚠️ Partial | ❌ None | ❌ None |
| **DI Ready** | ✅ Native | ⚠️ Manual | ✅ Native | ❌ No | ❌ No |
| **Testability** | ✅ Excellent | ✅ Good | ✅ Excellent | ⚠️ Manual | ⚠️ Manual |
| **Extensibility** | ✅ High | ✅ High | ✅ Medium | ⚠️ Low | ⚠️ Low |
| **Modularity** | ✅ High | ⚠️ Medium | ⚠️ Framework | N/A | ⚠️ Low |

### Performance

| Aspect | Concept DBAL | Doctrine DBAL | Laravel | PDO | Medoo |
|--------|-------------|---------------|---------|-----|-------|
| **Overhead** | Very Low | Medium | Medium | None | Very Low |
| **Memory** | Low | High | Medium | Minimal | Low |
| **Speed** | Fast | Medium | Fast | Fastest | Fast |
| **Optimization** | Prototype Pattern | Caching | Query Caching | N/A | Minimal |

## Use Case Recommendations

### Choose Concept DBAL When:

✅ **Modern PHP Projects (8.2+)**
- You want to leverage latest PHP features
- Type safety is important to you
- You value clean, modern code

✅ **Clean Architecture Applications**
- You follow SOLID principles
- Interface-driven design matters
- Testability is crucial

✅ **Concept Ecosystem Users**
- Using Singularity for DI
- Using other Concept packages
- Want seamless integration

✅ **Performance-Critical Applications**
- Need lightweight DB layer
- Want minimal overhead
- Query building without ORM weight

✅ **Microservices**
- Need focused, lightweight library
- Want minimal dependencies
- Prefer standalone components

### Choose Doctrine DBAL When:

✅ **Enterprise Applications**
- Need comprehensive features
- Schema management required
- Multiple database platforms

✅ **Symfony Projects**
- Already using Symfony
- Want ecosystem integration
- Need proven solution

✅ **Complex Schema Needs**
- Database migrations required
- Schema introspection needed
- Multi-platform support

### Choose Laravel Query Builder When:

✅ **Laravel Applications**
- Using Laravel framework
- Want framework integration
- Need Eloquent compatibility

✅ **Rapid Development**
- Quick prototyping
- Built-in features (pagination, etc.)
- Large community support

### Choose PDO When:

✅ **Maximum Performance**
- Every millisecond counts
- Simple queries only
- No abstraction needed

✅ **Legacy Projects**
- Working with old codebase
- Can't add dependencies
- Need raw SQL control

### Choose Medoo When:

✅ **Simple Projects**
- Basic CRUD operations
- Minimal dependencies
- Quick prototyping

✅ **Learning Projects**
- Teaching SQL concepts
- Simple API needed
- Lightweight solution

## Migration Guide

### From Doctrine DBAL to Concept DBAL

```php
// Doctrine DBAL - Using expression builder
$qb = $connection->createQueryBuilder();
$users = $qb->select('*')
    ->from('users')
    ->where('status = :status')
    ->setParameter('status', 'active')
    ->fetchAllAssociative();

// Concept DBAL - Using expression builder (recommended)
$users = $dml->select('*')
    ->from('users')
    ->where($dml->expr()->condition('status', '=', 'active'))
    ->execute();

// Concept DBAL - Using named placeholders (for migration compatibility)
$users = $dml->select('*')
    ->from('users')
    ->where($dml->expr()->raw('status = :status'))
    ->bind(['status' => 'active'])
    ->execute();
```

**Named Placeholders Migration:**

Doctrine DBAL's `setParameter()` pattern can be directly translated to Concept DBAL's `bind()` method:

```php
// Doctrine DBAL
$qb->where('age > :min_age AND status = :status')
   ->setParameter('min_age', 18)
   ->setParameter('status', 'active');

// Concept DBAL - Direct equivalent
$dml->select('*')
    ->from('users')
    ->where($dml->expr()->raw('age > :min_age AND status = :status'))
    ->bind([
        'min_age' => 18,
        'status' => 'active'
    ])
    ->execute();

// Concept DBAL - Recommended (using expression builder)
$dml->select('*')
    ->from('users')
    ->where($dml->expr()->condition('age', '>', 18))
    ->where($dml->expr()->condition('status', '=', 'active'))
    ->execute();
```

### From Laravel to Concept DBAL

```php
// Laravel
$users = DB::table('users')
    ->where('status', 'active')
    ->orderBy('name')
    ->get();

// Concept DBAL
$users = $dml->select('*')
    ->from('users')
    ->where($dml->expr()->condition('status', '=', 'active'))
    ->orderBy('name')
    ->execute();
```

### From PDO to Concept DBAL

```php
// PDO
$stmt = $pdo->prepare('SELECT * FROM users WHERE status = ?');
$stmt->execute(['active']);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Concept DBAL
$users = $dml->select('*')
    ->from('users')
    ->where($dml->expr()->condition('status', '=', 'active'))
    ->execute();
```

## Conclusion

**Concept DBAL** is ideal for:
- Modern PHP applications (8.2+)
- Projects valuing clean architecture
- Users of Concept ecosystem
- Performance-conscious developers
- Standalone query building needs

**Key Differentiators:**
1. **Modern PHP** - Built for PHP 8.2+
2. **Interface-Driven** - All components are interfaces
3. **DI-Native** - Designed for dependency injection
4. **Lightweight** - Focused on query building
5. **Clean Architecture** - SOLID principles throughout

Choose Concept DBAL when you want a modern, lightweight, clean architecture-focused database abstraction layer that leverages the latest PHP features and integrates seamlessly with dependency injection.

## Next Steps

- **[Quick Start](quickstart.md)** - Get started with Concept DBAL
- **[Architecture](architecture.md)** - Understand the design
- **[Best Practices](best-practices.md)** - Learn recommended patterns
- **[Examples](examples.md)** - See real-world usage
