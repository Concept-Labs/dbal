# DBAL Tests

This directory contains unit and integration tests for the DBAL package.

## Test Structure

```
tests/
├── Unit/              # Unit tests for individual components
│   ├── DML/          # DML-related unit tests
│   ├── DDL/          # DDL-related unit tests
│   └── ...           # Other unit tests
├── Integration/       # Integration tests
│   ├── DML/          # DML integration tests
│   └── DDL/          # DDL integration tests
└── Pest.php          # Pest configuration
```

## Running Tests

### All Tests

Run all tests with PHPUnit:
```bash
composer test
```

Run all tests with Pest:
```bash
composer test:pest
```

### Specific Test Suites

Run only unit tests:
```bash
vendor/bin/phpunit --testsuite=Unit
```

Run only integration tests:
```bash
vendor/bin/phpunit --testsuite=Integration
```

### With Coverage

Generate HTML coverage report:
```bash
composer test:coverage
```

The coverage report will be generated in the `coverage/` directory.

## Test Frameworks

### PHPUnit

PHPUnit tests are located in files ending with `Test.php` (e.g., `DmlManagerTest.php`).

Example:
```php
namespace Tests\Unit\DML;

use PHPUnit\Framework\TestCase;

class DmlManagerTest extends TestCase
{
    public function testSelectReturnsSelectBuilder(): void
    {
        // Test implementation
    }
}
```

### Pest

Pest tests are located in files ending with `PestTest.php` (e.g., `DmlManagerPestTest.php`).

Example:
```php
it('returns a select builder when calling select', function () {
    // Test implementation
    expect($result)->toBeInstanceOf(SelectBuilderInterface::class);
});
```

## Writing Tests

### Unit Tests

Unit tests should:
- Test a single component in isolation
- Mock all dependencies
- Be fast and independent
- Cover edge cases and error conditions

Example:
```php
public function testSelectReturnsSelectBuilder(): void
{
    $selectBuilder = $this->createMock(SelectBuilderInterface::class);
    $this->selectFactory->expects($this->once())
        ->method('create')
        ->willReturn($selectBuilder);

    $result = $this->dmlManager->select('*');

    $this->assertInstanceOf(SelectBuilderInterface::class, $result);
}
```

### Integration Tests

Integration tests should:
- Test component interactions
- Use real dependencies where appropriate
- Test database operations (with test database)
- Verify end-to-end workflows

Example:
```php
public function testCompleteSelectQuery(): void
{
    $result = $this->dmlManager->select('*')
        ->from('users')
        ->where('status', '=', 'active')
        ->execute();

    $this->assertInstanceOf(ResultInterface::class, $result);
}
```

## Test Data

### Mocking

Use PHPUnit mocks or Mockery for creating test doubles:

```php
// PHPUnit mock
$mock = $this->createMock(InterfaceName::class);

// Mockery mock
$mock = Mockery::mock(InterfaceName::class);
```

### Factories

Consider creating test factories for complex objects:

```php
class UserFactory
{
    public static function create(array $attributes = []): array
    {
        return array_merge([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ], $attributes);
    }
}
```

## Best Practices

1. **Test Naming**: Use descriptive test names that explain what is being tested
   ```php
   testSelectReturnsSelectBuilder()  // Good
   testSelect()                       // Bad
   ```

2. **Arrange-Act-Assert**: Structure tests in three parts
   ```php
   // Arrange
   $mock = $this->createMock(Interface::class);
   
   // Act
   $result = $service->method();
   
   // Assert
   $this->assertEquals($expected, $result);
   ```

3. **One Assertion Per Test**: Each test should verify one specific behavior
   
4. **Mock External Dependencies**: Don't rely on external services in unit tests

5. **Use Data Providers**: Test multiple scenarios with data providers
   ```php
   /**
    * @dataProvider statusProvider
    */
   public function testStatusValidation($status, $expected): void
   {
       // Test implementation
   }
   ```

## Continuous Integration

Tests are automatically run in CI/CD pipelines. Ensure all tests pass before merging:

```bash
# Run all tests
composer test

# Run with coverage
composer test:coverage

# Check code style (if configured)
composer lint
```

## Debugging Tests

### Run a Single Test

PHPUnit:
```bash
vendor/bin/phpunit --filter testMethodName
```

Pest:
```bash
vendor/bin/pest --filter "test description"
```

### Verbose Output

```bash
vendor/bin/phpunit --verbose
vendor/bin/pest --verbose
```

### Debug with PHPUnit

Add `--debug` flag:
```bash
vendor/bin/phpunit --debug
```

## Coverage Requirements

Aim for:
- **Minimum 80% code coverage** for critical components
- **100% coverage** for business logic
- Focus on meaningful tests, not just coverage numbers

## Contributing

When adding new features:
1. Write tests first (TDD approach recommended)
2. Ensure tests pass locally
3. Add both unit and integration tests where appropriate
4. Update this README if adding new test patterns or utilities
