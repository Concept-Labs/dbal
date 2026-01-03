# Building on DBAL: Patterns and Implementations

## Understanding DBAL's Role

**Concept DBAL is NOT:**
- ❌ An ORM (Object-Relational Mapper)
- ❌ An ActiveRecord implementation
- ❌ A high-level database abstraction with models
- ❌ A complete data layer solution

**Concept DBAL IS:**
- ✅ A **low-level query builder** and SQL abstraction tool
- ✅ A **foundation** for building ORMs, ActiveRecord, and other patterns
- ✅ A **building block** for custom database solutions
- ✅ A **type-safe SQL construction** library

## DBAL as a Foundation

Think of DBAL as the **building blocks** you use to create higher-level abstractions:

```
┌─────────────────────────────────────────────┐
│     Your Application Layer                  │
│  (ActiveRecord, ORM, Repository, etc.)      │
├─────────────────────────────────────────────┤
│                                             │
│     ← Built on top of DBAL                  │
│                                             │
├─────────────────────────────────────────────┤
│        Concept DBAL (Low-Level Tool)        │
│     Query Builder + SQL Expression          │
├─────────────────────────────────────────────┤
│         Database Connection (PDO)           │
└─────────────────────────────────────────────┘
```

DBAL provides the **primitives** - you build the **patterns** you need on top.

## Building an ActiveRecord Pattern

Here's how to implement ActiveRecord on top of DBAL:

### Base ActiveRecord Class

```php
<?php
namespace App\Database;

use Concept\DBAL\DbalManagerInterface;

abstract class ActiveRecord
{
    protected static ?DbalManagerInterface $dbal = null;
    protected array $attributes = [];
    protected array $original = [];
    protected bool $exists = false;
    
    abstract protected static function table(): string;
    abstract protected static function primaryKey(): string;
    
    public static function setDbal(DbalManagerInterface $dbal): void
    {
        static::$dbal = $dbal;
    }
    
    protected static function dbal(): DbalManagerInterface
    {
        if (static::$dbal === null) {
            throw new \RuntimeException('DBAL not set. Call setDbal() first.');
        }
        return static::$dbal;
    }
    
    // FIND methods - using DBAL
    public static function find(int $id): ?static
    {
        $table = static::table();
        $pk = static::primaryKey();
        
        $results = static::dbal()->dml()
            ->select('*')
            ->from($table)
            ->where(static::dbal()->dml()->expr()->condition($pk, '=', $id))
            ->limit(1)
            ->execute();
        
        if (empty($results)) {
            return null;
        }
        
        $instance = new static();
        $instance->fill($results[0]);
        $instance->exists = true;
        $instance->original = $instance->attributes;
        
        return $instance;
    }
    
    public static function all(): array
    {
        $results = static::dbal()->dml()
            ->select('*')
            ->from(static::table())
            ->execute();
        
        return array_map(function($row) {
            $instance = new static();
            $instance->fill($row);
            $instance->exists = true;
            $instance->original = $instance->attributes;
            return $instance;
        }, $results);
    }
    
    public static function where(string $column, string $operator, mixed $value): array
    {
        $results = static::dbal()->dml()
            ->select('*')
            ->from(static::table())
            ->where(static::dbal()->dml()->expr()->condition($column, $operator, $value))
            ->execute();
        
        return array_map(function($row) {
            $instance = new static();
            $instance->fill($row);
            $instance->exists = true;
            $instance->original = $instance->attributes;
            return $instance;
        }, $results);
    }
    
    // SAVE method - using DBAL
    public function save(): bool
    {
        if ($this->exists) {
            return $this->update();
        }
        return $this->insert();
    }
    
    protected function insert(): bool
    {
        $this->attributes['created_at'] = date('Y-m-d H:i:s');
        $this->attributes['updated_at'] = date('Y-m-d H:i:s');
        
        static::dbal()->dml()
            ->insert(static::table())
            ->values($this->attributes)
            ->execute();
        
        $id = static::dbal()->getConnection()->lastInsertId();
        $this->attributes[static::primaryKey()] = (int) $id;
        $this->exists = true;
        $this->original = $this->attributes;
        
        return true;
    }
    
    protected function update(): bool
    {
        $pk = static::primaryKey();
        $id = $this->attributes[$pk];
        
        // Get changed attributes
        $changes = array_diff_assoc($this->attributes, $this->original);
        if (empty($changes)) {
            return true; // No changes
        }
        
        $changes['updated_at'] = date('Y-m-d H:i:s');
        
        static::dbal()->dml()
            ->update(static::table())
            ->set($changes)
            ->where(static::dbal()->dml()->expr()->condition($pk, '=', $id))
            ->execute();
        
        $this->original = $this->attributes;
        
        return true;
    }
    
    // DELETE method - using DBAL
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }
        
        $pk = static::primaryKey();
        $id = $this->attributes[$pk];
        
        static::dbal()->dml()
            ->delete(static::table())
            ->where(static::dbal()->dml()->expr()->condition($pk, '=', $id))
            ->execute();
        
        $this->exists = false;
        
        return true;
    }
    
    // Attribute methods
    protected function fill(array $attributes): void
    {
        $this->attributes = $attributes;
    }
    
    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }
    
    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }
}
```

### Using the ActiveRecord Implementation

```php
<?php
namespace App\Models;

use App\Database\ActiveRecord;

class User extends ActiveRecord
{
    protected static function table(): string
    {
        return 'users';
    }
    
    protected static function primaryKey(): string
    {
        return 'id';
    }
    
    // Custom query methods built on DBAL
    public static function findByEmail(string $email): ?static
    {
        $results = static::dbal()->dml()
            ->select('*')
            ->from(static::table())
            ->where(static::dbal()->dml()->expr()->condition('email', '=', $email))
            ->limit(1)
            ->execute();
        
        if (empty($results)) {
            return null;
        }
        
        $instance = new static();
        $instance->fill($results[0]);
        $instance->exists = true;
        $instance->original = $instance->attributes;
        
        return $instance;
    }
    
    public static function active(): array
    {
        return static::where('status', '=', 'active');
    }
}

// Setup
User::setDbal($dbal);

// Usage - ActiveRecord pattern on top of DBAL
$user = User::find(1);
$user->name = 'John Updated';
$user->save();

$newUser = new User();
$newUser->name = 'Jane Doe';
$newUser->email = 'jane@example.com';
$newUser->save();

$user->delete();

$activeUsers = User::active();
```

## Building a Simple ORM

Here's how to build a basic ORM on DBAL:

### Base Model Class

```php
<?php
namespace App\ORM;

use Concept\DBAL\DbalManagerInterface;

abstract class Model
{
    protected static DbalManagerInterface $dbal;
    protected array $data = [];
    
    abstract public static function tableName(): string;
    
    public static function setDbal(DbalManagerInterface $dbal): void
    {
        static::$dbal = $dbal;
    }
    
    // Query Builder - returns QueryBuilder, not results
    public static function query(): QueryBuilder
    {
        return new QueryBuilder(static::class, static::$dbal);
    }
    
    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }
    
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }
}
```

### Query Builder (ORM Layer)

```php
<?php
namespace App\ORM;

use Concept\DBAL\DbalManagerInterface;

class QueryBuilder
{
    private string $modelClass;
    private DbalManagerInterface $dbal;
    private array $wheres = [];
    private array $orders = [];
    private ?int $limit = null;
    private ?int $offset = null;
    
    public function __construct(string $modelClass, DbalManagerInterface $dbal)
    {
        $this->modelClass = $modelClass;
        $this->dbal = $dbal;
    }
    
    public function where(string $column, string $operator, mixed $value): static
    {
        $this->wheres[] = compact('column', 'operator', 'value');
        return $this;
    }
    
    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $this->orders[] = compact('column', 'direction');
        return $this;
    }
    
    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }
    
    public function offset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }
    
    public function get(): array
    {
        // Build query using DBAL
        $query = $this->dbal->dml()
            ->select('*')
            ->from($this->modelClass::tableName());
        
        // Add WHERE clauses using DBAL expression
        foreach ($this->wheres as $where) {
            $query->where(
                $this->dbal->dml()->expr()->condition(
                    $where['column'],
                    $where['operator'],
                    $where['value']
                )
            );
        }
        
        // Add ORDER BY using DBAL
        foreach ($this->orders as $order) {
            $query->orderBy($order['column'], $order['direction']);
        }
        
        // Add LIMIT/OFFSET using DBAL
        if ($this->limit !== null) {
            $query->limit($this->limit);
        }
        if ($this->offset !== null) {
            $query->offset($this->offset);
        }
        
        // Execute and hydrate models
        $results = $query->execute();
        
        return array_map(function($row) {
            $model = new $this->modelClass();
            foreach ($row as $key => $value) {
                $model->$key = $value;
            }
            return $model;
        }, $results);
    }
    
    public function first(): ?object
    {
        $results = $this->limit(1)->get();
        return $results[0] ?? null;
    }
    
    public function count(): int
    {
        // Use DBAL aggregate function
        $result = $this->dbal->dml()
            ->select($this->dbal->dml()->expr()->count('*', 'total'))
            ->from($this->modelClass::tableName())
            ->execute();
        
        return (int) $result[0]['total'];
    }
}
```

### Using the ORM

```php
<?php
namespace App\Models;

use App\ORM\Model;

class Product extends Model
{
    public static function tableName(): string
    {
        return 'products';
    }
}

// Setup
Product::setDbal($dbal);

// Usage - ORM pattern built on DBAL
$products = Product::query()
    ->where('price', '>', 100)
    ->where('status', '=', 'active')
    ->orderBy('name', 'ASC')
    ->limit(10)
    ->get();

$product = Product::query()
    ->where('sku', '=', 'ABC-123')
    ->first();

$count = Product::query()
    ->where('category', '=', 'Electronics')
    ->count();
```

## Building a Data Mapper Pattern

```php
<?php
namespace App\Mapper;

use Concept\DBAL\DbalManagerInterface;

class UserMapper
{
    public function __construct(
        private DbalManagerInterface $dbal
    ) {}
    
    public function find(int $id): ?UserEntity
    {
        $results = $this->dbal->dml()
            ->select('*')
            ->from('users')
            ->where($this->dbal->dml()->expr()->condition('id', '=', $id))
            ->limit(1)
            ->execute();
        
        if (empty($results)) {
            return null;
        }
        
        return $this->hydrate($results[0]);
    }
    
    public function findAll(): array
    {
        $results = $this->dbal->dml()
            ->select('*')
            ->from('users')
            ->execute();
        
        return array_map([$this, 'hydrate'], $results);
    }
    
    public function save(UserEntity $user): void
    {
        if ($user->getId()) {
            $this->update($user);
        } else {
            $this->insert($user);
        }
    }
    
    private function insert(UserEntity $user): void
    {
        $data = $this->extract($user);
        
        $this->dbal->dml()
            ->insert('users')
            ->values($data)
            ->execute();
        
        $id = $this->dbal->getConnection()->lastInsertId();
        $user->setId((int) $id);
    }
    
    private function update(UserEntity $user): void
    {
        $data = $this->extract($user);
        
        $this->dbal->dml()
            ->update('users')
            ->set($data)
            ->where($this->dbal->dml()->expr()->condition('id', '=', $user->getId()))
            ->execute();
    }
    
    public function delete(UserEntity $user): void
    {
        $this->dbal->dml()
            ->delete('users')
            ->where($this->dbal->dml()->expr()->condition('id', '=', $user->getId()))
            ->execute();
    }
    
    private function hydrate(array $data): UserEntity
    {
        return new UserEntity(
            id: $data['id'],
            name: $data['name'],
            email: $data['email'],
            createdAt: new \DateTime($data['created_at'])
        );
    }
    
    private function extract(UserEntity $user): array
    {
        return [
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}

// Entity (Plain PHP object)
class UserEntity
{
    public function __construct(
        private ?int $id = null,
        private ?string $name = null,
        private ?string $email = null,
        private ?\DateTime $createdAt = null
    ) {}
    
    public function getId(): ?int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
}

// Usage
$mapper = new UserMapper($dbal);

$user = $mapper->find(1);
$user->setName('Updated Name');
$mapper->save($user);
```

## Building a Table Data Gateway

```php
<?php
namespace App\Gateway;

use Concept\DBAL\DbalManagerInterface;

class UserGateway
{
    public function __construct(
        private DbalManagerInterface $dbal
    ) {}
    
    public function findById(int $id): ?array
    {
        $results = $this->dbal->dml()
            ->select('*')
            ->from('users')
            ->where($this->dbal->dml()->expr()->condition('id', '=', $id))
            ->limit(1)
            ->execute();
        
        return $results[0] ?? null;
    }
    
    public function findByStatus(string $status): array
    {
        return $this->dbal->dml()
            ->select('*')
            ->from('users')
            ->where($this->dbal->dml()->expr()->condition('status', '=', $status))
            ->execute();
    }
    
    public function insert(array $data): int
    {
        $this->dbal->dml()
            ->insert('users')
            ->values($data)
            ->execute();
        
        return (int) $this->dbal->getConnection()->lastInsertId();
    }
    
    public function update(int $id, array $data): bool
    {
        return $this->dbal->dml()
            ->update('users')
            ->set($data)
            ->where($this->dbal->dml()->expr()->condition('id', '=', $id))
            ->execute();
    }
    
    public function delete(int $id): bool
    {
        return $this->dbal->dml()
            ->delete('users')
            ->where($this->dbal->dml()->expr()->condition('id', '=', $id))
            ->execute();
    }
}
```

## Building a Query Object Pattern

```php
<?php
namespace App\Query;

use Concept\DBAL\DbalManagerInterface;

interface QueryInterface
{
    public function execute(): mixed;
}

class FindActiveUsersQuery implements QueryInterface
{
    public function __construct(
        private DbalManagerInterface $dbal,
        private int $limit = 10
    ) {}
    
    public function execute(): array
    {
        return $this->dbal->dml()
            ->select('id', 'name', 'email')
            ->from('users')
            ->where($this->dbal->dml()->expr()->condition('status', '=', 'active'))
            ->where($this->dbal->dml()->expr()->condition('deleted_at', 'IS', null))
            ->orderBy('created_at', 'DESC')
            ->limit($this->limit)
            ->execute();
    }
}

class UserStatisticsQuery implements QueryInterface
{
    public function __construct(
        private DbalManagerInterface $dbal
    ) {}
    
    public function execute(): array
    {
        return $this->dbal->dml()
            ->select(
                'status',
                $this->dbal->dml()->expr()->count('*', 'total'),
                $this->dbal->dml()->expr()->avg('age', 'avg_age')
            )
            ->from('users')
            ->groupBy('status')
            ->execute();
    }
}

// Usage
$query = new FindActiveUsersQuery($dbal, limit: 20);
$users = $query->execute();

$statsQuery = new UserStatisticsQuery($dbal);
$statistics = $statsQuery->execute();
```

## Key Takeaways

1. **DBAL is the foundation, not the solution** - You build your patterns on top
2. **Maximum flexibility** - Implement exactly the pattern you need
3. **No magic** - Clear, explicit code for data operations
4. **Type-safe building blocks** - Use DBAL's query builder as your base
5. **Framework-agnostic** - Works with any architecture you choose

## Building Repository Pattern

The Repository pattern provides a collection-like interface for accessing domain objects:

```php
<?php
namespace App\Repository;

use Concept\DBAL\DbalManagerInterface;

interface RepositoryInterface
{
    public function find(int $id): ?object;
    public function findAll(): array;
    public function save(object $entity): void;
    public function delete(object $entity): void;
}

class UserRepository implements RepositoryInterface
{
    public function __construct(
        private DbalManagerInterface $dbal
    ) {}
    
    public function find(int $id): ?User
    {
        $results = $this->dbal->dml()
            ->select('*')
            ->from('users')
            ->where($this->dbal->dml()->expr()->condition('id', '=', $id))
            ->limit(1)
            ->execute();
        
        if (empty($results)) {
            return null;
        }
        
        return $this->hydrate($results[0]);
    }
    
    public function findAll(): array
    {
        $results = $this->dbal->dml()
            ->select('*')
            ->from('users')
            ->execute();
        
        return array_map([$this, 'hydrate'], $results);
    }
    
    public function findBy(array $criteria): array
    {
        $query = $this->dbal->dml()
            ->select('*')
            ->from('users');
        
        foreach ($criteria as $field => $value) {
            $query->where(
                $this->dbal->dml()->expr()->condition($field, '=', $value)
            );
        }
        
        $results = $query->execute();
        return array_map([$this, 'hydrate'], $results);
    }
    
    public function findOneBy(array $criteria): ?User
    {
        $results = $this->findBy($criteria);
        return $results[0] ?? null;
    }
    
    public function save(object $entity): void
    {
        if (!$entity instanceof User) {
            throw new \InvalidArgumentException('Entity must be a User');
        }
        
        $data = $this->extract($entity);
        
        if ($entity->getId()) {
            // Update existing
            $this->dbal->dml()
                ->update('users')
                ->set($data)
                ->where($this->dbal->dml()->expr()->condition('id', '=', $entity->getId()))
                ->execute();
        } else {
            // Insert new
            $this->dbal->dml()
                ->insert('users')
                ->values($data)
                ->execute();
            
            $entity->setId((int) $this->dbal->getConnection()->lastInsertId());
        }
    }
    
    public function delete(object $entity): void
    {
        if (!$entity instanceof User) {
            throw new \InvalidArgumentException('Entity must be a User');
        }
        
        $this->dbal->dml()
            ->delete('users')
            ->where($this->dbal->dml()->expr()->condition('id', '=', $entity->getId()))
            ->execute();
    }
    
    // Custom repository methods
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }
    
    public function findActiveUsers(): array
    {
        return $this->findBy(['status' => 'active']);
    }
    
    public function countByStatus(string $status): int
    {
        $result = $this->dbal->dml()
            ->select($this->dbal->dml()->expr()->count('*', 'total'))
            ->from('users')
            ->where($this->dbal->dml()->expr()->condition('status', '=', $status))
            ->execute();
        
        return (int) $result[0]['total'];
    }
    
    private function hydrate(array $data): User
    {
        return new User(
            id: $data['id'] ?? null,
            name: $data['name'],
            email: $data['email'],
            status: $data['status']
        );
    }
    
    private function extract(User $user): array
    {
        return [
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'status' => $user->getStatus(),
        ];
    }
}

// Entity
class User
{
    public function __construct(
        private ?int $id = null,
        private ?string $name = null,
        private ?string $email = null,
        private ?string $status = 'active'
    ) {}
    
    public function getId(): ?int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }
}

// Usage
$repository = new UserRepository($dbal);

// Find operations
$user = $repository->find(1);
$allUsers = $repository->findAll();
$activeUsers = $repository->findActiveUsers();
$user = $repository->findByEmail('john@example.com');

// Save operations
$newUser = new User(name: 'Jane', email: 'jane@example.com');
$repository->save($newUser);

// Update
$user->setStatus('inactive');
$repository->save($user);

// Delete
$repository->delete($user);

// Count
$count = $repository->countByStatus('active');
```

## Building Collection Classes

Collections provide an object-oriented interface for working with result sets:

```php
<?php
namespace App\Collection;

use Concept\DBAL\DbalManagerInterface;

class Collection implements \IteratorAggregate, \Countable, \ArrayAccess
{
    protected array $items = [];
    
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }
    
    public function all(): array
    {
        return $this->items;
    }
    
    public function first(): mixed
    {
        return $this->items[0] ?? null;
    }
    
    public function last(): mixed
    {
        return $this->items[count($this->items) - 1] ?? null;
    }
    
    public function filter(callable $callback): static
    {
        return new static(array_filter($this->items, $callback));
    }
    
    public function map(callable $callback): static
    {
        return new static(array_map($callback, $this->items));
    }
    
    public function pluck(string $key): array
    {
        return array_column($this->items, $key);
    }
    
    public function where(string $key, mixed $value): static
    {
        return $this->filter(function($item) use ($key, $value) {
            return $item[$key] === $value;
        });
    }
    
    public function sortBy(string $key, bool $descending = false): static
    {
        $items = $this->items;
        usort($items, function($a, $b) use ($key, $descending) {
            $result = $a[$key] <=> $b[$key];
            return $descending ? -$result : $result;
        });
        return new static($items);
    }
    
    public function groupBy(string $key): array
    {
        $groups = [];
        foreach ($this->items as $item) {
            $groups[$item[$key]][] = $item;
        }
        return array_map(fn($items) => new static($items), $groups);
    }
    
    public function isEmpty(): bool
    {
        return empty($this->items);
    }
    
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }
    
    // Countable
    public function count(): int
    {
        return count($this->items);
    }
    
    // IteratorAggregate
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }
    
    // ArrayAccess
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }
    
    public function offsetGet($offset): mixed
    {
        return $this->items[$offset];
    }
    
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }
    
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }
}

// Repository with Collection support
class CollectionRepository
{
    public function __construct(
        private DbalManagerInterface $dbal
    ) {}
    
    public function findAll(): Collection
    {
        $results = $this->dbal->dml()
            ->select('*')
            ->from('users')
            ->execute();
        
        return new Collection($results);
    }
    
    public function findActive(): Collection
    {
        $results = $this->dbal->dml()
            ->select('*')
            ->from('users')
            ->where($this->dbal->dml()->expr()->condition('status', '=', 'active'))
            ->execute();
        
        return new Collection($results);
    }
}

// Usage
$repository = new CollectionRepository($dbal);

$users = $repository->findAll();

// Collection operations
$activeUsers = $users->where('status', 'active');
$emails = $users->pluck('email');
$sortedUsers = $users->sortBy('created_at', descending: true);
$grouped = $users->groupBy('status');

// Iterate
foreach ($users as $user) {
    echo $user['name'] . "\n";
}

// Filter and map
$names = $users
    ->filter(fn($u) => $u['age'] > 18)
    ->map(fn($u) => $u['name'])
    ->all();

// Count
echo "Total users: " . $users->count();
```

## Building Entity Collections

Typed collections for specific entities:

```php
<?php
namespace App\Collection;

class UserCollection extends Collection
{
    public function __construct(array $users = [])
    {
        // Validate that all items are User objects
        foreach ($users as $user) {
            if (!$user instanceof User) {
                throw new \InvalidArgumentException('All items must be User instances');
            }
        }
        parent::__construct($users);
    }
    
    public function getActive(): self
    {
        return $this->filter(fn(User $user) => $user->getStatus() === 'active');
    }
    
    public function getInactive(): self
    {
        return $this->filter(fn(User $user) => $user->getStatus() === 'inactive');
    }
    
    public function getEmails(): array
    {
        return array_map(fn(User $user) => $user->getEmail(), $this->items);
    }
    
    public function findByEmail(string $email): ?User
    {
        foreach ($this->items as $user) {
            if ($user->getEmail() === $email) {
                return $user;
            }
        }
        return null;
    }
    
    public function saveAll(UserRepository $repository): void
    {
        foreach ($this->items as $user) {
            $repository->save($user);
        }
    }
}

// Repository returning typed collection
class UserRepository
{
    public function __construct(
        private DbalManagerInterface $dbal
    ) {}
    
    public function findAll(): UserCollection
    {
        $results = $this->dbal->dml()
            ->select('*')
            ->from('users')
            ->execute();
        
        $users = array_map([$this, 'hydrate'], $results);
        return new UserCollection($users);
    }
    
    private function hydrate(array $data): User
    {
        return new User(
            id: $data['id'],
            name: $data['name'],
            email: $data['email'],
            status: $data['status']
        );
    }
}

// Usage
$repository = new UserRepository($dbal);
$users = $repository->findAll();

$activeUsers = $users->getActive();
$emails = $users->getEmails();

foreach ($activeUsers as $user) {
    $user->setStatus('verified');
}

$activeUsers->saveAll($repository);
```

## Building Specification Pattern

Complex query logic encapsulated in specification objects:

```php
<?php
namespace App\Specification;

use Concept\DBAL\DML\Expression\SqlExpressionInterface;
use Concept\DBAL\DbalManagerInterface;

interface SpecificationInterface
{
    public function toExpression(DbalManagerInterface $dbal): SqlExpressionInterface;
}

class ActiveUserSpecification implements SpecificationInterface
{
    public function toExpression(DbalManagerInterface $dbal): SqlExpressionInterface
    {
        return $dbal->dml()->expr()->condition('status', '=', 'active');
    }
}

class AdultUserSpecification implements SpecificationInterface
{
    public function toExpression(DbalManagerInterface $dbal): SqlExpressionInterface
    {
        return $dbal->dml()->expr()->condition('age', '>=', 18);
    }
}

class EmailDomainSpecification implements SpecificationInterface
{
    public function __construct(private string $domain) {}
    
    public function toExpression(DbalManagerInterface $dbal): SqlExpressionInterface
    {
        return $dbal->dml()->expr()->like('email', "%@{$this->domain}");
    }
}

class AndSpecification implements SpecificationInterface
{
    private array $specifications;
    
    public function __construct(SpecificationInterface ...$specifications)
    {
        $this->specifications = $specifications;
    }
    
    public function toExpression(DbalManagerInterface $dbal): SqlExpressionInterface
    {
        $expressions = array_map(
            fn($spec) => $spec->toExpression($dbal),
            $this->specifications
        );
        
        $result = array_shift($expressions);
        foreach ($expressions as $expr) {
            $result = $dbal->dml()->expr()->group($result, 'AND', $expr);
        }
        
        return $result;
    }
}

class OrSpecification implements SpecificationInterface
{
    private array $specifications;
    
    public function __construct(SpecificationInterface ...$specifications)
    {
        $this->specifications = $specifications;
    }
    
    public function toExpression(DbalManagerInterface $dbal): SqlExpressionInterface
    {
        $expressions = array_map(
            fn($spec) => $spec->toExpression($dbal),
            $this->specifications
        );
        
        $result = array_shift($expressions);
        foreach ($expressions as $expr) {
            $result = $dbal->dml()->expr()->group($result, 'OR', $expr);
        }
        
        return $result;
    }
}

// Repository with Specification support
class SpecificationRepository
{
    public function __construct(
        private DbalManagerInterface $dbal
    ) {}
    
    public function findBySpecification(SpecificationInterface $spec): array
    {
        return $this->dbal->dml()
            ->select('*')
            ->from('users')
            ->where($spec->toExpression($this->dbal))
            ->execute();
    }
}

// Usage
$repository = new SpecificationRepository($dbal);

// Simple specification
$activeUsers = $repository->findBySpecification(
    new ActiveUserSpecification()
);

// Combined specifications
$activeAdults = $repository->findBySpecification(
    new AndSpecification(
        new ActiveUserSpecification(),
        new AdultUserSpecification()
    )
);

// Complex logic
$targetUsers = $repository->findBySpecification(
    new AndSpecification(
        new ActiveUserSpecification(),
        new OrSpecification(
            new EmailDomainSpecification('example.com'),
            new EmailDomainSpecification('test.com')
        )
    )
);
```

## What You Can Build on DBAL

- ✅ ActiveRecord implementations
- ✅ Data Mapper pattern
- ✅ Table Data Gateway
- ✅ Repository pattern
- ✅ Collection classes
- ✅ Entity collections
- ✅ Specification pattern
- ✅ Query Object pattern
- ✅ Custom ORMs
- ✅ CQRS implementations
- ✅ Event sourcing data layer
- ✅ Unit of Work pattern
- ✅ Identity Map pattern
- ✅ Lazy loading mechanisms
- ✅ Any other database abstraction pattern

## Next Steps

- **[Examples](examples.md)** - More implementation patterns
- **[Best Practices](best-practices.md)** - Building robust data layers
- **[Architecture](architecture.md)** - Understanding DBAL design
- **[Quick Start](quickstart.md)** - Learning the DBAL basics
