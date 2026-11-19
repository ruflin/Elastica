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
  - Add tests for any new or changed behaviour. Every bug fix needs a
    regression test that was seen failing without the fix; see
    [Bug fixes](#bug-fixes).
  - Update `CHANGELOG.md` for every functional change (test-only changes excluded); see [Changelog](#changelog).
  - Open pull requests against `9.x` (the default branch) from a feature branch. Never push to `9.x` directly (see `CONTRIBUTING.md`).
  - Keep pull requests as small as possible: one change per PR. Split larger work into a series of small, independently reviewable PRs. For example, land a refactor separately from the feature that needs it, and keep unrelated cleanups out of feature PRs.

## Environment

- PHP 8.1–8.6 (matches the CI matrix and `composer.json`). The Docker PHP
  container runs 8.1, the lowest supported version, so don't use syntax or
  functions added after PHP 8.1.
- Elasticsearch 9.x (CI and `docker/compose.es.yaml` default to `9.5.5`)
- Composer, Make, Docker / Docker Compose

## Key commands

- `make docker-start` / `make docker-stop` / `make docker-shell`
  - Add `DOCKER_OPTIONS="--detach"` to run in the background.
  - Pin the ES version with `ES_VERSION=9.5.5`; pass the same value to `make docker-stop`.
- `make docker-run-phpunit` — run all tests
- `make docker-run-phpunit PHPUNIT_OPTIONS="--group=unit"` — unit tests (no ES needed)
- `make docker-run-phpunit PHPUNIT_OPTIONS="--group=functional"` — functional tests (ES must be running)
- `make docker-run-phpunit PHPUNIT_OPTIONS="--filter=ClientTest"` — filter by name
- `make docker-run-phpcs` / `make docker-fix-phpcs` — coding standards
- `make docker-run-phpstan` — static analysis
- `make docker-run-rector` / `make docker-fix-rector` — Rector
- `make composer-install` / `make composer-update`

Gotchas:

- `docker-run-phpstan` and `docker-run-rector` call `vendor/bin/*` without
  installing dependencies first. On a fresh checkout, run
  `make docker-run-phpunit` once, or run `make composer-install` inside
  `make docker-shell`. Installing on the host can resolve dependencies for a
  newer PHP than the container's 8.1.

## Checks before finishing

CI runs these gates. Run the ones your change can affect before you report it done:

- PHP-CS-Fixer: `make docker-run-phpcs`
- PHPStan: `make docker-run-phpstan`
- Rector (dry run): `make docker-run-rector`
- PHPUnit: `make docker-run-phpunit` with ES running, or `--group=unit` if it is not
- `composer.json` normalisation: CI runs `composer-normalize` (no `make` target)

## Project overview

Elastica is a PHP client for Elasticsearch with an object-oriented architecture.
Sources live under `src/` (PSR-4 `Elastica\\`); tests under `tests/`
(PSR-4 `Elastica\Test\\`).

## Coding standards

- `declare(strict_types=1);` everywhere.
- All classes live in the `Elastica` namespace. Follow the existing public API
  design when deciding on visibility and `final`. No class in `src/` is
  currently `final`. Adding `final` or narrowing visibility is a BC break, so
  only do it deliberately and record it under Backward Compatibility Breaks.
- All methods, properties, and parameters typed; PHPDoc where it adds info.
- Coding style: PHP-CS-Fixer with `@PSR2`, `@Symfony`, `@PhpCsFixer`,
  `@PHP80Migration(:risky)`, `@PHPUnit100Migration:risky` rule sets.
- Static analysis: PHPStan level 5 must pass over `src/` and `tests/`. Fix new
  errors rather than adding them to `phpstan-baseline.neon`.
- Rector dry run must pass (`rector.php`).
- Testing: PHPUnit 10.5.

## Architecture

Core components: `Client` (entry point, implements `Elastic\Elasticsearch\ClientInterface`
and reuses traits/transport from the official `elasticsearch-php` client),
`Index`, `Search`, `Query`, `Document`.

Key namespaces:

- `Query/` — query types extending `AbstractQuery`.
- `Aggregation/` — analytics with shared traits.
- `Bulk/` — bulk operations.
- `ResultSet/` — result processing pipeline.
- `Exception/` — exception hierarchy (`ClientException`, `BulkException`,
  `InvalidException`, `NotFoundException`).
- `Script/`, `QueryBuilder/`.

Patterns in use: factory (`Query::create`), strategy (`ResultSet/BuilderInterface`,
implemented by `DefaultBuilder`), shared `Param` base for parameter handling,
traits for reuse.

## Testing requirements

- Test layout generally mirrors `src/` under `tests/`.
- Test classes extend `Elastica\Test\Base` (directly or via a shared base such
  as `BaseAggregationTestCase`) and live in `Elastica\Test`.
  `ClientConfigurationTest` extends `TestCase` directly and so skips the group
  check in `Base::setUp`. It declares its group explicitly; don't copy it.
- Method names start with `test`.
- Declare one group per test via the PHPUnit attribute `#[Group('unit')]`,
  `#[Group('functional')]`, or `#[Group('benchmark')]`. `Base::setUp` fails any
  test that has none of the three; it does not reject tests that declare more
  than one.
- `phpunit.xml.dist` fails the run on warnings, deprecations and risky tests,
  and runs tests in random order. Tests must be warning-free, make assertions,
  and not depend on each other.
- Tests that call deprecated APIs must call `$this->hideDeprecated()`, or the
  test class must extend `DeprecatedClassBase`. Otherwise the deprecation fails
  the run.
- Functional tests run `DELETE /_all` in `Base::tearDown`, which removes every
  index in the cluster. Only run them against the local Docker cluster or another
  disposable one, never a shared cluster.

### Bug fixes

Every bug fix needs a regression test in the same PR, and you must verify that
the test was red before the fix and is green with it:

1. Write the test and run it against the unfixed `src/`. Revert only `src/`
   (for example `git checkout 9.x -- src/`, or set the fix aside in a temporary
   WIP commit; never bare `git stash`).
2. Confirm it fails because of the bug, with an assertion on the wrong
   behaviour, not because of a syntax error, missing fixture or setup problem.
3. Restore the fix and confirm the test passes.
4. In the PR description, state that the test failed without the fix and passes
   with it, and quote the failing message in one line.

Exempt are fixes with no testable behaviour (documentation, CI, comments,
type-only changes). Say so in the PR description instead. If a test can't be
made to fail deterministically (for example it depends on cluster timing), say
that too rather than claiming the check.

## Changelog

- Add entries under `## [Unreleased]` in `CHANGELOG.md`, in the matching
  Keep a Changelog heading (`Added`, `Changed`, `Deprecated`, `Removed`, `Fixed`,
  `Security`).
- Put breaking changes under `### Backward Compatibility Breaks`, and say why
  in the PR description (see `CONTRIBUTING.md`).
- Match the style of existing entries and link the PR or issue, for example
  `* Added support for ... [#2293](https://github.com/ruflin/Elastica/pull/2293)`.
  If the PR number isn't known yet, add the link once the PR is open.

## Configuration files

- `composer.json` — dependencies and autoloading (PSR-4; keep normalised)
- `phpstan.neon` / `phpstan-baseline.neon` — static analysis config and known-issue baseline
- `.php-cs-fixer.dist.php` — coding-style rules
- `rector.php` — Rector rules for `src/` and `tests/`
- `phpunit.xml.dist` — PHPUnit config (strict warnings, deprecations and risky tests)
- `docker/compose.*.yaml` — development environment
- `.github/workflows/continuous-integration.yaml` — the CI gates (source of truth for what must pass)
