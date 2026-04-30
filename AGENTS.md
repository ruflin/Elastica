# Agents

Guidance for AI agents working in this repository.

## Identity

- **Persona**: AI assistant specialised in PHP and Elasticsearch development.
- **Purpose**: Help maintain and extend the Elastica library with high standards
  for code quality, testing, and documentation.
- **Operating rules**:
  - Follow the project's coding standards and architectural patterns.
  - Use the Docker-based development environment.
  - Preserve backward compatibility and type safety.
  - Add tests for any new or changed behaviour.
  - Update `CHANGELOG.md` for every functional change (test-only changes excluded).

## Environment

- PHP 8.1–8.5
- Elasticsearch 9.x
- Composer, Make, Docker / Docker Compose

## Key commands

- `make docker-start` / `make docker-stop` / `make docker-shell`
- `make docker-run-phpunit` — run all tests
- `make docker-run-phpunit PHPUNIT_OPTIONS="--group=unit"` — unit tests
- `make docker-run-phpunit PHPUNIT_OPTIONS="--group=functional"` — functional tests
- `make docker-run-phpunit PHPUNIT_OPTIONS="--filter=ClientTest"` — filter by name
- `make docker-run-phpcs` / `make docker-fix-phpcs` — coding standards
- `make docker-run-phpstan` — static analysis
- `make composer-install` / `make composer-update`

## Project overview

Elastica is a PHP client for Elasticsearch with an object-oriented architecture.
Sources live under `src/` (PSR-4 `Elastica\\`); tests under `tests/`
(PSR-4 `Elastica\Test\\`).

## Coding standards

- `declare(strict_types=1);` everywhere.
- All classes in the `Elastica` namespace; `final` unless designed for extension.
- All methods, properties, and parameters typed; PHPDoc where it adds info.
- Coding style: PHP-CS-Fixer with `@PSR2`, `@Symfony`, `@PhpCsFixer`,
  `@PHP80Migration(:risky)`, `@PHPUnit100Migration:risky` rule sets.
- Static analysis: PHPStan level 5 must pass.
- Testing: PHPUnit 10.5; methods need `@covers` and `@group unit|functional`.

## Architecture

Core components: `Client` (entry point, extends `elasticsearch-php`), `Index`,
`Search`, `Query`, `Document`.

Key namespaces:

- `Query/` — query types extending `AbstractQuery`.
- `Aggregation/` — analytics with shared traits.
- `Bulk/` — bulk operations.
- `ResultSet/` — result processing pipeline.
- `Exception/` — exception hierarchy (`ClientException`, `BulkException`,
  `InvalidException`, `NotFoundException`).
- `Script/`, `QueryBuilder/`.

Patterns in use: factory (`Query::create`), strategy (`BuilderInterface`),
shared `Param` base for parameter handling, traits for reuse.

## Testing requirements

- Test layout mirrors `src/` under `tests/`.
- Test classes extend `Elastica\Test\Base` and live in `Elastica\Test`.
- Method names start with `test`; require `@covers` and a `@group` annotation.

## Configuration files

- `composer.json` — dependencies and autoloading
- `phpstan.neon` — static analysis config
- `.php-cs-fixer.dist.php` — coding-style rules
- `docker/compose.*.yaml` — development environment
