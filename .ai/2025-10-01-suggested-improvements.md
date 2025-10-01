# feat: Suggested Repository Improvements

## Summary

Based on comprehensive repository review, here are recommended improvements to enhance code quality, maintainability, and AI agent compatibility.

## 1. PHPUnit Configuration Enhancement

### Current State
PHPUnit configuration exists but doesn't enforce coverage metadata.

### Recommended Change
Update `phpunit.xml.dist` to enforce @covers annotations:

```xml
<phpunit
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
    bootstrap="tests/bootstrap.php"
    requireCoverageMetadata="true"
    beStrictAboutCoverageMetadata="true"
    colors="true"
    ...
>
```

**Benefits:**
- Enforces @covers annotations on all tests
- Fails tests that lack coverage metadata
- Improves code coverage accuracy

## 2. Add Final Keyword Strategically

### Classes That Should Be Final

Non-abstract classes not designed for extension:

```php
// src/Client.php
final class Client implements ClientInterface

// src/Document.php  
final class Document extends AbstractUpdateAction

// src/Search.php
final class Search implements SearchableInterface

// src/Index.php
final class Index implements SearchableInterface
```

### Classes That Should NOT Be Final

- All abstract classes (AbstractQuery, AbstractScript, etc.)
- Classes explicitly designed for extension
- Test base classes (Base, BaseAggregationTestCase)

**Action Items:**
1. Audit all concrete classes
2. Add `final` to classes not meant for extension
3. Document extension points clearly

## 3. Improve Method Documentation

### Add Missing Docblocks

```php
// BEFORE
protected function _getBaseName()
{
    $shortName = (new \ReflectionClass($this))->getShortName();
    return Util::toSnakeCase($shortName);
}

// AFTER
/**
 * Get the base name for this query/filter.
 * Removes 'Query' suffix and converts to snake_case.
 *
 * @return string The base name in snake_case format
 */
protected function _getBaseName(): string
{
    $shortName = (new \ReflectionClass($this))->getShortName();
    return Util::toSnakeCase($shortName);
}
```

## 4. Modernize Property Naming

### Current Convention (Old Style)
```php
protected $_config;
protected $_data;
protected $_logger;
```

### Recommended Convention (Modern PHP)
```php
protected ClientConfiguration $config;
protected array $data;
protected LoggerInterface $logger;
```

**Migration Strategy:**
1. Mark old properties as @deprecated
2. Add new properties with modern names
3. Update in major version bump to avoid BC breaks

## 5. Add CI Checks

### New GitHub Actions Workflow

Create `.github/workflows/quality-checks.yml`:

```yaml
name: Quality Checks

on: [pull_request]

jobs:
  test-coverage:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Check @covers annotations
        run: |
          # Fail if any test method lacks @covers
          make docker-run-phpunit PHPUNIT_OPTIONS="--stop-on-failure"
  
  final-keyword-check:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Check for missing final keywords
        run: |
          # Custom script to verify final keyword usage
          php tools/check-final-classes.php
```

## 6. Documentation Improvements

### Add to CONTRIBUTING.md

```markdown
## Test Requirements

All test methods MUST include:

1. `@covers` annotation specifying the method being tested
2. Group attribute: `#[Group('unit')]` or `#[Group('functional')]`
3. Extend `Elastica\Test\Base`

Example:
\`\`\`php
/**
 * @covers \Elastica\Client::getIndex
 */
#[Group('unit')]
public function testGetIndex(): void
{
    // test implementation
}
\`\`\`

See `.ai/2025-10-01-test-template-with-covers.md` for complete examples.
```

## 7. Add PHP 8 Features

### Property Promotion

```php
// BEFORE
private LoggerInterface $_logger;

public function __construct(LoggerInterface $logger)
{
    $this->_logger = $logger;
}

// AFTER (PHP 8.0+)
public function __construct(
    private LoggerInterface $logger
) {
}
```

### Readonly Properties (PHP 8.1+)

```php
public function __construct(
    private readonly LoggerInterface $logger,
    private readonly ClientConfiguration $config,
) {
}
```

## 8. Enhanced Type Safety

### Union Types for Query::create()

```php
// src/Query.php
public static function create(
    string|array|AbstractQuery $query
): self {
    // implementation
}
```

### Nullable Type Hints

```php
// Make nullability explicit
public function setId(?string $id): self
{
    $this->id = $id;
    return $this;
}
```

## 9. Trait Organization

### Create Reusable Traits for Common Patterns

```php
// src/Traits/HasBoostTrait.php
trait HasBoostTrait
{
    public function setBoost(float $boost): self
    {
        return $this->setParam('boost', $boost);
    }
    
    public function getBoost(): ?float
    {
        return $this->getParam('boost');
    }
}
```

Apply to queries that support boost:
```php
class TermQuery extends AbstractQuery
{
    use HasBoostTrait;
    // ...
}
```

## 10. Error Message Improvements

### Make Exceptions More Informative

```php
// BEFORE
throw new InvalidException('Invalid data');

// AFTER
throw new InvalidException(
    sprintf(
        'Invalid data provided for field "%s". Expected array or string, got %s',
        $field,
        get_debug_type($data)
    )
);
```

## Implementation Priority

### High Priority (Complete in 1-2 sprints)
1. ✅ Fix AGENTS.md documentation (COMPLETED)
2. ✅ Create .ai directory (COMPLETED)  
3. ⏳ Add @covers to all tests
4. ⏳ Enforce coverage metadata in PHPUnit config

### Medium Priority (Complete in 3-4 sprints)
5. Add final keyword to applicable classes
6. Improve method documentation
7. Add CI quality checks

### Low Priority (Future releases)
8. Modernize property naming (BC break - major version)
9. Adopt PHP 8 features fully
10. Extract common patterns to traits

## Success Metrics

- [ ] 100% of tests have @covers annotations
- [ ] 95%+ compliance with AGENTS.md requirements
- [ ] All concrete classes evaluated for final keyword
- [ ] Zero documentation gaps in public APIs
- [ ] CI enforces all quality standards

## Questions for Review

1. Should we enforce final keyword immediately or in next major version?
2. Priority order for test annotation updates - by directory or by importance?
3. Timeline for property naming modernization?
4. Should we create automated migration tools?
