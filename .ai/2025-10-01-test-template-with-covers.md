# Test Template with @covers Annotations

## Correct Test Format

All tests should follow this format to comply with AGENTS.md requirements:

```php
<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Exception\InvalidException;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class ClientTest extends BaseTest
{
    /**
     * @covers \Elastica\Client::__construct
     */
    #[Group('unit')]
    public function testConstruct(): void
    {
        $client = new Client();
        $this->assertInstanceOf(Client::class, $client);
    }

    /**
     * @covers \Elastica\Client::getIndex
     */
    #[Group('unit')]
    public function testGetIndex(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        
        $this->assertInstanceOf(\Elastica\Index::class, $index);
    }

    /**
     * @covers \Elastica\Client::addDocuments
     */
    #[Group('functional')]
    public function testAddDocuments(): void
    {
        // Functional test implementation
    }
}
```

## Key Requirements

1. ✅ **@covers annotation** - REQUIRED for every test method
   - Format: `@covers \Full\Namespace\ClassName::methodName`
   - Place before the Group attribute

2. ✅ **Group attribute** - REQUIRED
   - Use `#[Group('unit')]` for unit tests
   - Use `#[Group('functional')]` for functional tests
   - Can have multiple groups if needed

3. ✅ **Strict types declaration** - REQUIRED
   - `declare(strict_types=1);` at top of file

4. ✅ **Extends BaseTest** - REQUIRED
   - All test classes must extend `Elastica\Test\Base`

5. ✅ **Internal annotation** - REQUIRED
   - Add `@internal` docblock to test class

## Migration Checklist

To update existing tests:

- [ ] Add `@covers` annotation to each test method
- [ ] Verify Group attribute is present (`#[Group('unit')]` or `#[Group('functional')]`)
- [ ] Ensure test class extends `Elastica\Test\Base`
- [ ] Check `declare(strict_types=1);` is present
- [ ] Add `@internal` to class docblock if missing

## Examples by Test Type

### Unit Test Example
```php
/**
 * @covers \Elastica\Query\BoolQuery::addShould
 */
#[Group('unit')]
public function testAddShould(): void
{
    $query = new BoolQuery();
    $termQuery = new Term(['field' => 'value']);
    
    $query->addShould($termQuery);
    
    $this->assertArrayHasKey('should', $query->toArray()['bool']);
}
```

### Functional Test Example
```php
/**
 * @covers \Elastica\Index::search
 * @covers \Elastica\Search::search
 */
#[Group('functional')]
public function testSearch(): void
{
    $index = $this->_createIndex();
    $query = new Query\MatchAll();
    
    $resultSet = $index->search($query);
    
    $this->assertInstanceOf(ResultSet::class, $resultSet);
}
```

### Multiple Methods Coverage
```php
/**
 * @covers \Elastica\Document::__construct
 * @covers \Elastica\Document::setData
 * @covers \Elastica\Document::getData
 */
#[Group('unit')]
public function testDocumentDataHandling(): void
{
    $data = ['field' => 'value'];
    $doc = new Document(null, $data);
    
    $this->assertEquals($data, $doc->getData());
}
```

## Automated Checking

Consider adding this to CI:

```xml
<!-- phpunit.xml.dist -->
<phpunit
    requireCoverageMetadata="true"
    ...
>
```

Or use this command to check:
```bash
vendor/bin/phpunit --coverage-text --coverage-filter src/
```
