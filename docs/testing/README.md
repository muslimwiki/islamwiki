# IslamWiki Testing Guidelines

## 🎯 **Overview**

This directory contains comprehensive testing documentation for IslamWiki, covering testing strategies, procedures, and best practices. All testing follows Islamic naming conventions and ensures code quality, security, and performance standards.

---

## 🏗️ **Testing Architecture**

### **Testing Pyramid**
```
Testing Strategy:
├── 📁 Unit Tests - Component-level testing (70%)
├── 📁 Integration Tests - System integration testing (20%)
├── 📁 Feature Tests - End-to-end user workflows (10%)
└── 📁 Performance Tests - Load and stress testing
```

### **Testing Principles**
- **Test First**: Write tests before implementation
- **Comprehensive Coverage**: Aim for 80%+ code coverage
- **Islamic Naming**: Use Islamic naming conventions in tests
- **Security Focus**: Security testing in all test types
- **Performance Testing**: Performance validation

---

## 🔧 **Test Categories**

### **1. Unit Tests**
- **Component Testing**: Individual class and method testing
- **Isolation**: Test components in isolation
- **Mocking**: Use mocks for dependencies
- **Fast Execution**: Quick test execution

### **2. Integration Tests**
- **System Integration**: Test component interactions
- **Database Testing**: Database integration testing
- **API Testing**: API endpoint testing
- **Middleware Testing**: Request/response pipeline testing

### **3. Feature Tests**
- **User Workflows**: Complete user journey testing
- **Browser Testing**: Real browser testing
- **Cross-browser**: Multiple browser compatibility
- **Mobile Testing**: Mobile device testing

---

## 📝 **Test Implementation**

### **Unit Test Example**
```php
<?php

declare(strict_types=1);

namespace IslamWiki\Tests\Unit\Core\Routing;

use IslamWiki\Core\Routing\Routing;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use PHPUnit\Framework\TestCase;

/**
 * Simplified Routing Unit Tests
 * 
 * @package IslamWiki\Tests\Unit\Core\Routing
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class ShahidTest_SimplifiedRouting extends TestCase
{
    private Routing $router;
    
    protected function setUp(): void
    {
        $this->router = new Routing();
    }
    
    /**
     * Test route registration
     */
    public function testRouteRegistration(): void
    {
        $handler = function() {
            return new Response('Test response');
        };
        
        $this->router->get('/test', $handler);
        
        $this->assertTrue($this->router->hasRoute('GET', '/test'));
    }
    
    /**
     * Test route handling
     */
    public function testRouteHandling(): void
    {
        $handler = function() {
            return new Response('Test response');
        };
        
        $this->router->get('/test', $handler);
        
        $request = new Request('GET', '/test');
        $response = $this->router->handle($request);
        
        $this->assertEquals('Test response', $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
    
    /**
     * Test middleware integration
     */
    public function testMiddlewareIntegration(): void
    {
        $middleware = function(Request $request, callable $next) {
            $response = $next($request);
            $response->setHeader('X-Test-Middleware', 'true');
            return $response;
        };
        
        $this->router->addMiddleware($middleware);
        
        $handler = function() {
            return new Response('Test response');
        };
        
        $this->router->get('/test', $handler);
        
        $request = new Request('GET', '/test');
        $response = $this->router->handle($request);
        
        $this->assertEquals('true', $response->getHeader('X-Test-Middleware'));
    }
}
```

### **Integration Test Example**
```php
/**
 * Integration Test Example
 */
class ShahidTest_Integration_UserAuthentication extends TestCase
{
    private TestDatabase $database;
    private Application $app;
    
    protected function setUp(): void
    {
        $this->database = new TestDatabase();
        $this->app = $this->createApplication();
    }
    
    /**
     * Test complete authentication flow
     */
    public function testCompleteAuthenticationFlow(): void
    {
        // Create test user
        $user = $this->createTestUser();
        
        // Test login
        $response = $this->post('/auth/login', [
            'username' => $user->username,
            'password' => 'testpassword'
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        // Test authenticated access
        $response = $this->get('/dashboard', [
            'Authorization' => 'Bearer ' . $this->getAuthToken()
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
    }
}
```

---

## 🧪 **Testing Tools & Framework**

### **PHPUnit Configuration**
```xml
<!-- phpunit.xml -->
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/9.5/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         processIsolation="false"
         stopOnFailure="false">
    
    <testsuites>
        <testsuite name="Unit Tests">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration Tests">
            <directory>tests/Integration</directory>
        </testsuite>
        <testsuite name="Feature Tests">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    
    <coverage>
        <include>
            <directory suffix=".php">src</directory>
        </include>
        <exclude>
            <directory>vendor</directory>
            <directory>tests</directory>
        </exclude>
    </coverage>
</phpunit>
```

### **Test Database Configuration**
```php
/**
 * Test Database Configuration
 */
class TestDatabase
{
    private PDO $connection;
    
    public function __construct()
    {
        $this->connection = new PDO(
            'mysql:host=localhost;dbname=islamwiki_test',
            'test_user',
            'test_password'
        );
        
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    /**
     * Setup test database
     */
    public function setup(): void
    {
        $this->runMigrations();
        $this->seedTestData();
    }
    
    /**
     * Cleanup test database
     */
    public function cleanup(): void
    {
        $this->truncateTables();
    }
}
```

---

## 🔒 **Security Testing**

### **Security Test Categories**
- **Authentication Testing**: Login and session security
- **Authorization Testing**: Access control validation
- **Input Validation Testing**: XSS and injection prevention
- **Output Security Testing**: Output escaping validation
- **API Security Testing**: API endpoint security

### **Security Test Implementation**
```php
/**
 * Security Test Example
 */
class ShahidTest_Security_InputValidation extends TestCase
{
    /**
     * Test XSS prevention
     */
    public function testXssPrevention(): void
    {
        $maliciousInput = '<script>alert("xss")</script>';
        
        $validator = new AmanValidationService();
        $result = $validator->validate(['content' => $maliciousInput], [
            'content' => 'required|safe_html'
        ]);
        
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('content', $result);
    }
    
    /**
     * Test SQL injection prevention
     */
    public function testSqlInjectionPrevention(): void
    {
        $maliciousInput = "'; DROP TABLE users; --";
        
        $user = new User();
        $user->username = $maliciousInput;
        
        // This should not cause SQL injection
        $this->assertInstanceOf(User::class, $user);
    }
}
```

---

## 📊 **Performance Testing**

### **Performance Test Categories**
- **Load Testing**: Normal load performance
- **Stress Testing**: High load performance
- **Endurance Testing**: Long-running performance
- **Spike Testing**: Sudden load changes

### **Performance Test Implementation**
```php
/**
 * Performance Test Example
 */
class ShahidTest_Performance_ApiResponse extends TestCase
{
    /**
     * Test API response time
     */
    public function testApiResponseTime(): void
    {
        $startTime = microtime(true);
        
        $response = $this->get('/api/quran/verses');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertLessThan(100, $responseTime); // Should respond in under 100ms
    }
    
    /**
     * Test database query performance
     */
    public function testDatabaseQueryPerformance(): void
    {
        $startTime = microtime(true);
        
        $verses = QuranVerse::where('surah_id', 1)->get();
        
        $endTime = microtime(true);
        $queryTime = ($endTime - $startTime) * 1000;
        
        $this->assertNotEmpty($verses);
        $this->assertLessThan(50, $queryTime); // Should query in under 50ms
    }
}
```

---

## 📚 **Testing Documentation**

### **Available Testing Guides**
- **[Unit Testing](unit/README.md)** - Component testing
- **[Integration Testing](integration/README.md)** - System testing
- **[Feature Testing](feature/README.md)** - User workflow testing
- **[Performance Testing](performance/README.md)** - Performance validation
- **[Security Testing](security/README.md)** - Security validation

### **Testing Development**
- **[Testing Standards](../standards.md)** - Development standards
- **[Style Guide](../guides/style-guide.md)** - Coding standards
- **[Islamic Naming Conventions](../guides/islamic-naming-conventions.md)** - Naming guide

---

## 🚀 **Test Execution**

### **Running Tests**
```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit --testsuite "Unit Tests"

# Run tests with coverage
./vendor/bin/phpunit --coverage-html coverage/

# Run tests in parallel
./vendor/bin/phpunit --parallel

# Run tests with specific filter
./vendor/bin/phpunit --filter testRouteRegistration
```

### **Continuous Integration**
```yaml
# .github/workflows/tests.yml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: ./vendor/bin/phpunit
      - name: Generate coverage
        run: ./vendor/bin/phpunit --coverage-clover coverage.xml
```

---

## 📖 **Additional Resources**

### **Related Documentation**
- **[Architecture Overview](../architecture/overview.md)** - System architecture
- **[Core Systems](../architecture/core-systems.md)** - System components
- **[Security Documentation](../security/README.md)** - Security guidelines
- **[API Documentation](../api/overview.md)** - API reference

### **Testing Resources**
- **[PHPUnit Documentation](https://phpunit.de/)** - PHPUnit testing framework
- **[Testing Best Practices](https://martinfowler.com/articles/practical-test-pyramid.html)** - Testing strategies
- **[Security Testing Guide](https://owasp.org/www-project-web-security-testing-guide/)** - Security testing

---

**Last Updated:** 2025-08-19  
**Version:** 0.0.1.0  
**Author:** IslamWiki Development Team  
**License:** AGPL-3.0  
**Status:** Testing Documentation Complete ✅ 