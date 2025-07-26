# GEMINI.md

This file provides guidance to Google Gemini CLI when working with code in this repository.

## Project Overview

Elastica is a PHP client library for Elasticsearch with a well-structured, object-oriented architecture. This is an open-source project that requires careful attention to code quality, testing, and documentation standards.

## General Instructions

- This project is written in PHP and requires PHP 8.1 or higher
- The project uses a Docker-based development environment for consistency
- All code must be compatible with Elasticsearch 9.0 or newer
- Each code change requires an entry in the `CHANGELOG.md` file (except test changes)
- All contributions must follow the established coding standards and architecture patterns

## Development Environment

### Required Tools
- Docker and Docker Compose for development environment
- PHP 8.1+ with required extensions
- Composer for dependency management
- Make for running common tasks

### Key Commands
- `make docker-start` - Start development environment
- `make docker-run-phpunit` - Run all tests
- `make docker-run-phpcs` - Check coding standards
- `make docker-fix-phpcs` - Fix coding standards automatically
- `make run-phpstan` - Run static analysis

## Coding Standards

### PHP Requirements
- All code must be PSR-2 compliant
- Strict type declarations required (`declare(strict_types=1);`)
- All classes must be in the `Elastica` namespace
- All classes must be final unless designed for extension
- All methods, properties, and parameters must have type declarations
- Comprehensive PHPDoc annotations required for all elements

### Documentation Standards
- All classes, methods, and properties must have docblocks
- Methods calling Elasticsearch APIs must include links to official Elasticsearch documentation
- Test methods must include `@covers` and `@group` annotations
- Use `@group unit` for unit tests and `@group functional` for functional tests

### Code Quality Tools
- PHPStan level 5 static analysis must pass
- php-cs-fixer enforces PSR-2 compliance
- PHPUnit 10.5 for testing with comprehensive coverage

## Architecture Guidelines

### Core Components
- **Client**: Central entry point extending official elasticsearch-php client
- **Index**: Represents Elasticsearch indices, implements SearchableInterface
- **Search**: Orchestrates search operations across indices
- **Query**: Container for query components using factory pattern
- **Document**: Represents individual Elasticsearch documents with change tracking

### Key Namespaces and Their Purpose
- `Query/`: All query types (BoolQuery, MatchQuery, TermQuery) extending AbstractQuery
- `Aggregation/`: Analytics functionality with shared traits for code reuse
- `Bulk/`: Bulk operation handling with individual actions and response processing
- `ResultSet/`: Result processing pipeline with BuilderInterface strategy pattern
- `Exception/`: Comprehensive error handling hierarchy
- `Script/`: Script query support and script field handling
- `QueryBuilder/`: DSL for programmatic query construction

### Design Patterns Used
- **Factory Pattern**: `Query::create()` handles multiple input types
- **Strategy Pattern**: `BuilderInterface` for flexible result set construction
- **Parameter Management**: The Base `Param` class provides a consistent interface for managing parameters across different components. It standardizes how parameters are set, retrieved, and validated, ensuring uniformity and reducing code duplication. This pattern is particularly useful for handling complex configurations and query parameters in a predictable and reusable manner.
- **Traits**: Extensive use for code reuse (BucketsPathTrait, GapPolicyTrait)

## Testing Requirements

### Test Structure
- Tests mirror `src/` structure under `tests/` directory
- All test classes must extend `Elastica\Test\Base`
- All test classes must be in the `Elastica\Test` namespace
- Test methods must start with `test` prefix

### Test Annotations
- All test methods require `@covers` annotation specifying covered method
- All test methods require `@group` annotation (unit and/or functional)
- Separate execution with `--group=unit` and `--group=functional`

### Test Execution
- Unit tests: `make docker-run-phpunit PHPUNIT_OPTIONS="--group=unit"`
- Functional tests: `make docker-run-phpunit PHPUNIT_OPTIONS="--group=functional"`
- Specific test class: `make docker-run-phpunit PHPUNIT_OPTIONS="tests/ClientTest.php"`

## Implementation Guidelines

### Query Construction Patterns
Support multiple query construction methods:
```php
// String query
$query = Query::create('search term');

// Array query
$query = Query::create(['match' => ['field' => 'value']]);

// Object query
$matchQuery = new MatchQuery('field', 'value');
$query = Query::create($matchQuery);
```

### Error Handling
Use the established exception hierarchy:
- `ClientException` for transport/client errors
- `BulkException` for bulk operation failures
- `InvalidException` for validation errors
- `NotFoundException` for missing resources

### Best Practices
- Maintain backward compatibility when possible
- Follow existing code patterns and conventions
- Use dependency injection where appropriate
- Implement proper parameter validation
- Ensure comprehensive test coverage
- Focus on flexibility and type safety

## File Organization

### Source Code Structure
- Main classes in `src/` following PSR-4 autoloading
- Interfaces define contracts (SearchableInterface, ArrayableInterface)
- Abstract classes provide common functionality
- Final classes implement specific features

### Configuration Files
- `composer.json` defines dependencies and autoloading
- `phpstan.neon` configures static analysis
- `.php-cs-fixer.dist.php` defines coding standards
- `docker-compose.yml` sets up development environment

The codebase emphasizes clean architecture, comprehensive testing, and maintainable code that integrates seamlessly with Elasticsearch functionality.