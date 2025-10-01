UPGRADE FROM 8.x to 9.0
=======================

This is a major release with breaking changes that require code updates.

PHP Version Requirements
-------------------------
* **Minimum PHP version**: 8.1 (previously 8.0)
* **Maximum PHP version**: 8.5 (newly supported)
* **Elasticsearch compatibility**: 9.0+ (previously 8.x)

Template API Changes
--------------------
The template API has been significantly reworked to align with Elasticsearch's new template system.

### IndexTemplate Changes
`Elastica\IndexTemplate` now works only with the new `_index_template` API.

*Before*
```php
use Elastica\IndexTemplate;

$template = new IndexTemplate($client, 'my-template');
$template->setBody(['settings' => ['number_of_shards' => 1]]);
$template->create();
```

*After*
```php
use Elastica\IndexTemplate;

$template = new IndexTemplate($client, 'my-template');
$template->setBody(['index_patterns' => ['my-*'], 'template' => ['settings' => ['number_of_shards' => 1]]]);
$template->create();
```

### Legacy Template Support
For legacy template API support, use the new `Elastica\Template` class.

*Before*
```php
use Elastica\IndexTemplate;

$template = new IndexTemplate($client, 'my-template');
// Using deprecated template API
```

*After*
```php
use Elastica\Template;

$template = new Template($client, 'my-template');
// Uses legacy template API
```

### New Template Classes
* `Elastica\ComponentTemplate` - For component templates
* `Elastica\IndexTemplate` - For index templates (new API)
* `Elastica\Template` - For legacy templates

SearchableInterface Method Signature Changes
--------------------------------------------
The `search()` and `count()` methods in `SearchableInterface` have been simplified.

*Before*
```php
$search = $index->search($query, $options, 'GET');
$count = $index->count($query, $options, 'GET');
```

*After*
```php
$search = $index->search($query, $options);
$count = $index->count($query, $options);
```

**Affected classes**: `Elastica\Search` and `Elastica\Index`

Removed Classes
---------------
* `Elastica\Request` - Constants are no longer used and the class is not needed

*Before*
```php
use Elastica\Request;

$method = Request::GET;
```

*After*
```php
// Use string literals directly
$method = 'GET';
```

PHP Version Requirements
-----------------------
* **Minimum PHP version**: 8.1 (increased from 8.0)
* **Maximum PHP version**: 8.5 (newly supported)

Update your `composer.json`:

*Before*
```json
{
    "require": {
        "php": "~8.0.0 || ~8.1.0 || ~8.2.0 || ~8.3.0"
    }
}
```

*After*
```json
{
    "require": {
        "php": "~8.1.0 || ~8.2.0 || ~8.3.0 || ~8.4.0 || ~8.5.0"
    }
}
```

Testing Framework Updates
-------------------------
* **PHPUnit**: Upgraded from 9.5 to 10.5
* **PHPStan**: Updated to 2.x

If you have custom test configurations, update them accordingly.

Migration Checklist
-------------------
- [ ] Update PHP version to 8.1+ (remove PHP 8.0 support)
- [ ] Update Elasticsearch to 9.0+
- [ ] Review template usage and migrate to new API if needed
- [ ] Remove third parameter from `search()` and `count()` method calls
- [ ] Replace `Elastica\Request` constants with string literals
- [ ] Update test configurations for PHPUnit 10.5
- [ ] Update static analysis tools to PHPStan 2.x

Breaking Changes Summary
------------------------
1. **PHP 8.0 support dropped** - Minimum PHP version is now 8.1
2. **Template API changes** - `IndexTemplate` now uses new API, use `Template` for legacy
3. **Method signature changes** - Removed unused `$method` parameter from search methods
4. **Removed classes** - `Elastica\Request` class no longer exists
5. **Elasticsearch 9.0+ required** - No longer compatible with Elasticsearch 8.x

For detailed information about all changes, see the [CHANGELOG.md](CHANGELOG.md#900).
