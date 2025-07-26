# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Testing
- Run all tests: `make docker-run-phpunit`
- Run specific test group: `make docker-run-phpunit PHPUNIT_OPTIONS="--group=unit"`
- Run specific test class: `make docker-run-phpunit PHPUNIT_OPTIONS="tests/ClientTest.php"`
- Filter tests by name: `make docker-run-phpunit PHPUNIT_OPTIONS="--filter=ClientTest"`

### Code Quality
- Check coding standards: `make docker-run-phpcs`
- Fix coding standards: `make docker-fix-phpcs`
- Run PHPStan analysis: `make run-phpstan`
- Fix PHPStan baseline: `make fix-phpstan-baseline`

### Docker Environment
- Start development environment: `make docker-start`
- Stop development environment: `make docker-stop`
- Access container shell: `make docker-shell`
- Start detached: `make docker-start DOCKER_OPTIONS="--detach"`

### Composer
- Install dependencies: `make composer-install`
- Update dependencies: `make composer-update`

## Architecture Overview

Elastica is a PHP client library for Elasticsearch with a well-structured, object-oriented architecture:

### Core Components
- **Client**: Central entry point that handles all Elasticsearch communication, extends official elasticsearch-php client
- **Index**: Represents an Elasticsearch index, implements SearchableInterface for index-specific operations
- **Search**: Orchestrates search operations across indices, manages search options and result building
- **Query**: Container for query components (query, aggregations, sorting), uses factory pattern for flexible construction
- **Document**: Represents individual Elasticsearch documents with change tracking and validation

### Key Namespaces
- `Query/`: All query types (BoolQuery, MatchQuery, TermQuery, etc.) extending AbstractQuery
- `Aggregation/`: Analytics functionality (Terms, DateHistogram, Avg, etc.) with shared traits
- `Bulk/`: Bulk operation handling with individual actions and response processing
- `ResultSet/`: Result processing pipeline with BuilderInterface strategy pattern
- `Exception/`: Comprehensive error handling hierarchy
- `Script/`: Script query support and script field handling
- `QueryBuilder/`: DSL for programmatic query construction with version awareness

### Design Patterns
- **Parameter Management**: Base `Param` class provides consistent `setParam()`/`getParam()` interface
- **Factory Pattern**: `Query::create()` handles multiple input types (string, array, objects)
- **Strategy Pattern**: `BuilderInterface` for flexible result set construction
- **Interface Segregation**: ArrayableInterface, NameableInterface, SearchableInterface
- **Traits**: Extensive use for code reuse (BucketsPathTrait, GapPolicyTrait, processor traits)

### Request/Response Flow
```
User Code → Index/Search → Query Assembly → Client → Transport → Elasticsearch
           ←           ← ResultSet      ← Response ←          ←
```

## Development Environment

Uses Docker for consistent development environment. The project supports Elasticsearch 9.x and requires PHP 8.1+.

### Test Structure
- Tests mirror src/ structure under `tests/`
- Uses PHPUnit 10.5 with groups: `unit`, `functional`
- Bootstrap: `tests/bootstrap.php` handles ES version normalization
- Coverage reports generated for both unit and functional tests

### Code Standards
- PSR-2 coding standards enforced via php-cs-fixer
- PHPStan level 5 static analysis
- Strict type declarations required (`declare(strict_types=1);`)
- Comprehensive PHPDoc type annotations
- All classes must be final unless meant to be extended
- All methods and properties must have type declarations
- Changelog entries required for all code changes (except tests)

## Key Implementation Notes

### Query Construction
Multiple ways to build queries:
```php
// String query
$query = Query::create('search term');

// Array query  
$query = Query::create(['match' => ['field' => 'value']]);

// Object query
$matchQuery = new MatchQuery('field', 'value');
$query = Query::create($matchQuery);
```

### Bulk Operations
Handled through `Bulk` class with individual actions:
- `CreateDocument`, `UpdateDocument`, `DeleteDocument`, `IndexDocument`
- Response processing through `ResponseSet` with detailed error handling

### Result Processing
- `ResultSet` implements Iterator, Countable, ArrayAccess
- Individual `Result` objects with lazy Document conversion
- Configurable builders for different processing needs

### Error Handling
Comprehensive exception hierarchy:
- `ClientException` for transport/client errors
- `BulkException` for bulk operation failures  
- `InvalidException` for validation errors
- `NotFoundException` for missing resources

The codebase emphasizes flexibility, type safety, and clean separation of concerns while maintaining backward compatibility.

## Additional Development Notes

### Documentation Requirements
- All methods calling Elasticsearch APIs must include links to official Elasticsearch documentation
- All classes, methods, and properties require comprehensive docblocks
- Test methods must include `@covers` and `@group` annotations

### Tool Chain
- Uses phive/phar for tool management (php-cs-fixer, etc.)
- Docker Compose environment with multiple service configurations
- Automated baseline management for PHPStan analysis