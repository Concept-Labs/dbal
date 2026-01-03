# Real-World Examples

This guide provides complete, real-world examples of using Concept DBAL in common scenarios.

## Table of Contents

- [User Management System](#user-management-system)
- [E-commerce Application](#e-commerce-application)
- [Blog Platform](#blog-platform)
- [Analytics Dashboard](#analytics-dashboard)
- [Multi-tenant Application](#multi-tenant-application)

## User Management System

### Complete User Repository

```php
<?php
namespace App\Repository;

use Concept\DBAL\DML\DmlManagerInterface;
use Concept\DBAL\Exception\DBALException;

class UserRepository
{
    public function __construct(
        private DmlManagerInterface $dml
    ) {}
    
    /**
     * Find user by ID
     */
    public function findById(int $id): ?array
    {
        $results = $this->dml->select('*')
            ->from('users')
            ->where($this->dml->expr()->condition('id', '=', $id))
            ->limit(1)
            ->execute();
            
        return $results[0] ?? null;
    }
    
    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        $results = $this->dml->select('*')
            ->from('users')
            ->where($this->dml->expr()->condition('email', '=', $email))
            ->limit(1)
            ->execute();
            
        return $results[0] ?? null;
    }
    
    /**
     * Get all active users with pagination
     */
    public function getActiveUsers(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        return $this->dml->select('id', 'name', 'email', 'created_at')
            ->from('users')
            ->where($this->dml->expr()->condition('status', '=', 'active'))
            ->where($this->dml->expr()->condition('deleted_at', 'IS', null))
            ->orderBy('created_at', 'DESC')
            ->limit($perPage)
            ->offset($offset)
            ->execute();
    }
    
    /**
     * Search users by name or email
     */
    public function search(string $query, int $limit = 10): array
    {
        $pattern = "%{$query}%";
        
        return $this->dml->select('id', 'name', 'email')
            ->from('users')
            ->where(
                $this->dml->expr()->group(
                    $this->dml->expr()->like('name', $pattern),
                    'OR',
                    $this->dml->expr()->like('email', $pattern)
                )
            )
            ->where($this->dml->expr()->condition('deleted_at', 'IS', null))
            ->orderBy('name')
            ->limit($limit)
            ->execute();
    }
    
    /**
     * Create new user
     */
    public function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->dml->insert('users')
            ->values($data)
            ->execute();
            
        return $this->dml->getConnection()->lastInsertId();
    }
    
    /**
     * Update user
     */
    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->dml->update('users')
            ->set($data)
            ->where($this->dml->expr()->condition('id', '=', $id))
            ->execute();
    }
    
    /**
     * Soft delete user
     */
    public function softDelete(int $id): bool
    {
        return $this->dml->update('users')
            ->set([
                'deleted_at' => date('Y-m-d H:i:s'),
                'status' => 'deleted'
            ])
            ->where($this->dml->expr()->condition('id', '=', $id))
            ->execute();
    }
    
    /**
     * Get user statistics
     */
    public function getStatistics(): array
    {
        return $this->dml->select(
                'status',
                $this->dml->expr()->count('*', 'count')
            )
            ->from('users')
            ->where($this->dml->expr()->condition('deleted_at', 'IS', null))
            ->groupBy('status')
            ->execute();
    }
}
```

### User Service

```php
<?php
namespace App\Service;

use App\Repository\UserRepository;
use Concept\DBAL\Exception\DBALException;

class UserService
{
    public function __construct(
        private UserRepository $repository
    ) {}
    
    public function registerUser(string $name, string $email, string $password): int
    {
        // Check if email exists
        if ($this->repository->findByEmail($email)) {
            throw new \RuntimeException('Email already exists');
        }
        
        // Create user
        return $this->repository->create([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'status' => 'pending',
            'email_verified' => false
        ]);
    }
    
    public function verifyEmail(int $userId): bool
    {
        return $this->repository->update($userId, [
            'email_verified' => true,
            'status' => 'active'
        ]);
    }
    
    public function deactivateInactiveUsers(int $days = 90): int
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$days} days"));
        
        return $this->repository->updateByCondition(
            ['status' => 'inactive'],
            $this->dml->expr()->group(
                $this->dml->expr()->condition('last_login', '<', $cutoffDate),
                'AND',
                $this->dml->expr()->condition('status', '=', 'active')
            )
        );
    }
}
```

## E-commerce Application

### Order Repository

```php
<?php
namespace App\Repository;

use Concept\DBAL\DML\DmlManagerInterface;

class OrderRepository
{
    public function __construct(
        private DmlManagerInterface $dml
    ) {}
    
    /**
     * Get orders with customer and item details
     */
    public function getOrderDetails(int $orderId): ?array
    {
        $results = $this->dml->select(
                'orders.*',
                'users.name as customer_name',
                'users.email as customer_email'
            )
            ->from('orders')
            ->join('users', $this->dml->expr()->condition('orders.user_id', '=', 'users.id'))
            ->where($this->dml->expr()->condition('orders.id', '=', $orderId))
            ->limit(1)
            ->execute();
            
        if (empty($results)) {
            return null;
        }
        
        $order = $results[0];
        
        // Get order items
        $order['items'] = $this->getOrderItems($orderId);
        
        return $order;
    }
    
    /**
     * Get order items
     */
    private function getOrderItems(int $orderId): array
    {
        return $this->dml->select(
                'order_items.*',
                'products.name as product_name',
                'products.sku as product_sku'
            )
            ->from('order_items')
            ->join('products', $this->dml->expr()->condition('order_items.product_id', '=', 'products.id'))
            ->where($this->dml->expr()->condition('order_items.order_id', '=', $orderId))
            ->execute();
    }
    
    /**
     * Get customer order history
     */
    public function getCustomerOrders(int $userId, int $limit = 10): array
    {
        return $this->dml->select(
                'orders.id',
                'orders.order_number',
                'orders.total',
                'orders.status',
                'orders.created_at',
                $this->dml->expr()->count('order_items.id', 'item_count')
            )
            ->from('orders')
            ->leftJoin('order_items', $this->dml->expr()->condition('orders.id', '=', 'order_items.order_id'))
            ->where($this->dml->expr()->condition('orders.user_id', '=', $userId))
            ->groupBy('orders.id')
            ->orderBy('orders.created_at', 'DESC')
            ->limit($limit)
            ->execute();
    }
    
    /**
     * Get revenue by date range
     */
    public function getRevenue(string $startDate, string $endDate): array
    {
        return $this->dml->select(
                'DATE(created_at) as date',
                $this->dml->expr()->sum('total', 'revenue'),
                $this->dml->expr()->count('*', 'order_count')
            )
            ->from('orders')
            ->where($this->dml->expr()->condition('status', '=', 'completed'))
            ->where($this->dml->expr()->condition('created_at', '>=', $startDate))
            ->where($this->dml->expr()->condition('created_at', '<=', $endDate))
            ->groupBy('DATE(created_at)')
            ->orderBy('date')
            ->execute();
    }
    
    /**
     * Get top selling products
     */
    public function getTopProducts(int $limit = 10): array
    {
        return $this->dml->select(
                'products.id',
                'products.name',
                'products.sku',
                $this->dml->expr()->sum('order_items.quantity', 'total_sold'),
                $this->dml->expr()->sum('order_items.quantity * order_items.price', 'revenue')
            )
            ->from('order_items')
            ->join('products', $this->dml->expr()->condition('order_items.product_id', '=', 'products.id'))
            ->join('orders', $this->dml->expr()->condition('order_items.order_id', '=', 'orders.id'))
            ->where($this->dml->expr()->condition('orders.status', '=', 'completed'))
            ->groupBy('products.id')
            ->orderBy('total_sold', 'DESC')
            ->limit($limit)
            ->execute();
    }
    
    /**
     * Create order with items
     */
    public function createOrder(int $userId, array $items, float $total): int
    {
        // Create order
        $orderNumber = 'ORD-' . date('YmdHis') . '-' . $userId;
        
        $this->dml->insert('orders')
            ->values([
                'user_id' => $userId,
                'order_number' => $orderNumber,
                'total' => $total,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ])
            ->execute();
            
        $orderId = $this->dml->getConnection()->lastInsertId();
        
        // Create order items
        $orderItems = [];
        foreach ($items as $item) {
            $orderItems[] = [
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ];
        }
        
        $this->dml->insert('order_items')
            ->values($orderItems)
            ->execute();
            
        return $orderId;
    }
}
```

## Blog Platform

### Post Repository

```php
<?php
namespace App\Repository;

use Concept\DBAL\DML\DmlManagerInterface;

class PostRepository
{
    public function __construct(
        private DmlManagerInterface $dml
    ) {}
    
    /**
     * Get published posts with author and category
     */
    public function getPublishedPosts(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        
        return $this->dml->select(
                'posts.id',
                'posts.title',
                'posts.slug',
                'posts.excerpt',
                'posts.published_at',
                'users.name as author_name',
                'categories.name as category_name',
                $this->dml->expr()->count('comments.id', 'comment_count')
            )
            ->from('posts')
            ->join('users', $this->dml->expr()->condition('posts.author_id', '=', 'users.id'))
            ->join('categories', $this->dml->expr()->condition('posts.category_id', '=', 'categories.id'))
            ->leftJoin('comments', $this->dml->expr()->condition('posts.id', '=', 'comments.post_id'))
            ->where($this->dml->expr()->condition('posts.status', '=', 'published'))
            ->where($this->dml->expr()->condition('posts.published_at', '<=', date('Y-m-d H:i:s')))
            ->groupBy('posts.id')
            ->orderBy('posts.published_at', 'DESC')
            ->limit($perPage)
            ->offset($offset)
            ->execute();
    }
    
    /**
     * Get post by slug with full details
     */
    public function getBySlug(string $slug): ?array
    {
        $results = $this->dml->select(
                'posts.*',
                'users.name as author_name',
                'users.email as author_email',
                'categories.name as category_name'
            )
            ->from('posts')
            ->join('users', $this->dml->expr()->condition('posts.author_id', '=', 'users.id'))
            ->join('categories', $this->dml->expr()->condition('posts.category_id', '=', 'categories.id'))
            ->where($this->dml->expr()->condition('posts.slug', '=', $slug))
            ->where($this->dml->expr()->condition('posts.status', '=', 'published'))
            ->limit(1)
            ->execute();
            
        if (empty($results)) {
            return null;
        }
        
        $post = $results[0];
        
        // Get tags
        $post['tags'] = $this->getPostTags($post['id']);
        
        // Increment view count
        $this->incrementViews($post['id']);
        
        return $post;
    }
    
    /**
     * Get post tags
     */
    private function getPostTags(int $postId): array
    {
        return $this->dml->select('tags.name')
            ->from('post_tags')
            ->join('tags', $this->dml->expr()->condition('post_tags.tag_id', '=', 'tags.id'))
            ->where($this->dml->expr()->condition('post_tags.post_id', '=', $postId))
            ->execute();
    }
    
    /**
     * Increment post views
     */
    private function incrementViews(int $postId): void
    {
        $this->dml->update('posts')
            ->set('views', 'views + 1')
            ->where($this->dml->expr()->condition('id', '=', $postId))
            ->execute();
    }
    
    /**
     * Search posts
     */
    public function search(string $query, ?int $categoryId = null): array
    {
        $pattern = "%{$query}%";
        
        $selectQuery = $this->dml->select(
                'posts.id',
                'posts.title',
                'posts.slug',
                'posts.excerpt',
                'categories.name as category_name'
            )
            ->from('posts')
            ->join('categories', $this->dml->expr()->condition('posts.category_id', '=', 'categories.id'))
            ->where(
                $this->dml->expr()->group(
                    $this->dml->expr()->like('posts.title', $pattern),
                    'OR',
                    $this->dml->expr()->like('posts.content', $pattern)
                )
            )
            ->where($this->dml->expr()->condition('posts.status', '=', 'published'));
            
        if ($categoryId) {
            $selectQuery->where($this->dml->expr()->condition('posts.category_id', '=', $categoryId));
        }
        
        return $selectQuery->orderBy('posts.published_at', 'DESC')
            ->limit(20)
            ->execute();
    }
    
    /**
     * Get related posts
     */
    public function getRelatedPosts(int $postId, int $categoryId, int $limit = 5): array
    {
        return $this->dml->select('id', 'title', 'slug', 'excerpt')
            ->from('posts')
            ->where($this->dml->expr()->condition('category_id', '=', $categoryId))
            ->where($this->dml->expr()->condition('id', '!=', $postId))
            ->where($this->dml->expr()->condition('status', '=', 'published'))
            ->orderBy('published_at', 'DESC')
            ->limit($limit)
            ->execute();
    }
}
```

## Analytics Dashboard

### Analytics Repository

```php
<?php
namespace App\Repository;

use Concept\DBAL\DML\DmlManagerInterface;

class AnalyticsRepository
{
    public function __construct(
        private DmlManagerInterface $dml
    ) {}
    
    /**
     * Get dashboard summary
     */
    public function getDashboardSummary(): array
    {
        // Total users
        $userStats = $this->dml->select(
                $this->dml->expr()->count('*', 'total'),
                $this->dml->expr()->sum('CASE WHEN status = "active" THEN 1 ELSE 0 END', 'active')
            )
            ->from('users')
            ->execute();
            
        // Total orders and revenue
        $orderStats = $this->dml->select(
                $this->dml->expr()->count('*', 'total_orders'),
                $this->dml->expr()->sum('total', 'total_revenue'),
                $this->dml->expr()->avg('total', 'avg_order_value')
            )
            ->from('orders')
            ->where($this->dml->expr()->condition('status', '=', 'completed'))
            ->execute();
            
        return [
            'users' => $userStats[0],
            'orders' => $orderStats[0]
        ];
    }
    
    /**
     * Get user growth over time
     */
    public function getUserGrowth(int $days = 30): array
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        return $this->dml->select(
                'DATE(created_at) as date',
                $this->dml->expr()->count('*', 'new_users')
            )
            ->from('users')
            ->where($this->dml->expr()->condition('created_at', '>=', $startDate))
            ->groupBy('DATE(created_at)')
            ->orderBy('date')
            ->execute();
    }
    
    /**
     * Get revenue by category
     */
    public function getRevenueByCategory(): array
    {
        return $this->dml->select(
                'categories.name',
                $this->dml->expr()->sum('order_items.quantity * order_items.price', 'revenue'),
                $this->dml->expr()->sum('order_items.quantity', 'units_sold')
            )
            ->from('order_items')
            ->join('products', $this->dml->expr()->condition('order_items.product_id', '=', 'products.id'))
            ->join('categories', $this->dml->expr()->condition('products.category_id', '=', 'categories.id'))
            ->join('orders', $this->dml->expr()->condition('order_items.order_id', '=', 'orders.id'))
            ->where($this->dml->expr()->condition('orders.status', '=', 'completed'))
            ->groupBy('categories.id')
            ->orderBy('revenue', 'DESC')
            ->execute();
    }
    
    /**
     * Get user retention cohort analysis
     */
    public function getCohortAnalysis(int $months = 6): array
    {
        // Get users grouped by signup month
        return $this->dml->select(
                'DATE_FORMAT(created_at, "%Y-%m") as cohort',
                $this->dml->expr()->count('*', 'total_users'),
                $this->dml->expr()->sum('CASE WHEN last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END', 'active_users')
            )
            ->from('users')
            ->where($this->dml->expr()->condition('created_at', '>=', date('Y-m-d', strtotime("-{$months} months"))))
            ->groupBy('cohort')
            ->orderBy('cohort')
            ->execute();
    }
}
```

## Multi-tenant Application

### Tenant-aware Repository

```php
<?php
namespace App\Repository;

use Concept\DBAL\DML\DmlManagerInterface;

class TenantRepository
{
    private ?int $currentTenantId = null;
    
    public function __construct(
        private DmlManagerInterface $dml
    ) {}
    
    public function setTenant(int $tenantId): void
    {
        $this->currentTenantId = $tenantId;
    }
    
    private function addTenantScope($query)
    {
        if ($this->currentTenantId === null) {
            throw new \RuntimeException('Tenant not set');
        }
        
        return $query->where(
            $this->dml->expr()->condition('tenant_id', '=', $this->currentTenantId)
        );
    }
    
    public function getUsers(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        $query = $this->dml->select('*')
            ->from('users')
            ->limit($perPage)
            ->offset($offset);
            
        return $this->addTenantScope($query)->execute();
    }
    
    public function createUser(array $data): int
    {
        $data['tenant_id'] = $this->currentTenantId;
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $this->dml->insert('users')
            ->values($data)
            ->execute();
            
        return $this->dml->getConnection()->lastInsertId();
    }
}
```

## Next Steps

- **[Query Builders](builders.md)** - Learn all builder methods
- **[SQL Expressions](expressions.md)** - Master expression system
- **[Best Practices](best-practices.md)** - Follow recommended patterns
- **[API Reference](api-reference.md)** - Complete method documentation
