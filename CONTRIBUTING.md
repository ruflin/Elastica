# Contributing

Help is very welcomed. Code contributions must follow the coding style enforced
by `php-cs-fixer` (see `.php-cs-fixer.dist.php`), which combines the `@PSR2`,
`@Symfony`, `@PhpCsFixer`, `@PHP80Migration:risky` and `@PHPUnit100Migration:risky`
rule sets. Run `make docker-fix-phpcs` to auto-format your changes.

See [`AGENTS.md`](./AGENTS.md) for a concise overview of the project's
architecture, key commands and testing requirements; both human contributors
and AI agents are expected to follow it.

All functional changes must be documented in
[`CHANGELOG.md`](./CHANGELOG.md). Test-only changes are exempt.

## Issues

* **Bugs & feature requests**: open an issue on
  [GitHub](https://github.com/ruflin/Elastica/issues). Search the existing
  issues first. Include a minimal reproduction, your Elastica version, and
  the targeted Elasticsearch version.
* **Security vulnerabilities**: do not open a public issue. See
  [`SECURITY.md`](./SECURITY.md) for the private disclosure process.
* **Questions**: open a question on
  [Stack Overflow](https://stackoverflow.com/questions/tagged/elastica) and
  tag it with `elastica`, or start a
  [GitHub Discussion](https://github.com/ruflin/Elastica/discussions).

## Setup

Elastica uses Docker for its development environment. Install
[Docker Engine](https://docs.docker.com/engine/install/) (which ships
the bundled `docker compose` v2 plugin); a separate `docker-compose` v1
binary is no longer required.

The repository ships several Compose files under `docker/` (PHP runner,
Elasticsearch cluster, optional reverse proxy) that the `make` targets
combine for you.

* `make docker-start` — pull and start PHP + Elasticsearch in the
  foreground. Logs stream from ES; `Ctrl-C` stops the stack.
* `make docker-start DOCKER_OPTIONS="--detach"` — start in detached mode.
* `make docker-stop` — stop the stack.

The Elasticsearch image is selected with the `ES_VERSION=` make
variable, e.g. `make docker-start ES_VERSION=9.1.0`. Use the **same**
`ES_VERSION` when stopping (`make docker-stop ES_VERSION=9.1.0`) so
Compose finds the matching project name.

### Local Docker configuration

Elasticsearch requires `vm.max_map_count >= 262144` on the host kernel.

* **Linux**: `sudo sysctl -w vm.max_map_count=262144`.
* **macOS / Windows with Docker Desktop**: the value is enforced inside
  the Linux VM that Docker Desktop manages. Recent versions of Docker
  Desktop already set a sufficient value out of the box; if you hit a
  bootstrap error, follow the steps from the
  [Docker Desktop troubleshooting guide](https://docs.docker.com/desktop/troubleshoot/topics/).

See the upstream
[Elasticsearch Docker reference](https://www.elastic.co/guide/en/elasticsearch/reference/current/docker.html#_set_vm_max_map_count_to_at_least_262144)
for additional details.

### Local commands
Check out the Makefile for other commands that can be used to run tests and other operations:
* Run your changes / tests in the virtual environment to make sure it is reproducible.
* Run the tests before creating the pull request using docker-compose locally.

### PHP Tools

Elastica uses [phive](https://phar.io/) to manage PHP tools and their
installation. The list of pinned tool versions lives in `phive.xml` and
is the source of truth.

Tools are downloaded into `tools/` on demand by the `make` targets that
need them (e.g. `install-phpcs` -> `tools/php-cs-fixer.phar`). You do
not have to invoke `phive` directly.

## Commands
The advantage in using the commands below is that no local tools and libraries have to be installed and it is guaranteed
that everyone is using the same tools.

The tools required for each command will be installed only when needed by the command itself.

### Coding standards
Run the command `make docker-run-phpcs` to run the coding-standard checks on the code inside the docker container.

You need to execute it before open a Pull Request: the CI will execute the same checks, to make sure that every PR
respects the same coding standards.

The command `make docker-fix-phpcs` can be used to fix the code automatically.

### Tests
Before running the tests inside the docker container, make sure to start them by running `make docker-start`.
See the "Setup" section above for further details.

Run the command `make docker-run-phpunit` to run the PHP tests on the code inside the docker container.

Options can be passed to `phpunit` when running in the docker container: use the `PHPUNIT_OPTIONS` to pass additional
arguments to the invocation of the tool.

Examples:
 - run a specific group of tests: `make docker-run-phpunit PHPUNIT_OPTIONS="--group=unit"`
 - filter the test to run: `make docker-run-phpunit PHPUNIT_OPTIONS="--filter=ClientTest"`
 - run tests for a specific test-class: `make docker-run-phpunit PHPUNIT_OPTIONS="tests/ClientTest.php"`

## Troubleshooting

### Version Compatibility Issues
If you encounter Elasticsearch version compatibility errors during testing:

1. **Check ES Version**: Ensure the Elasticsearch version matches the branch expectations:
   - Branch 9.x expects ES 9.x
   - Branch 8.x expects ES 8.x

2. **Set Correct ES Version**: Use the `ES_VERSION` parameter when starting Docker:
   ```bash
   make docker-start ES_VERSION=9.1.0
   ```

3. **Version Mismatch Errors**: If you see errors like "Accept version must be either version 8 or 7, but found 9":
   - Stop containers: `make docker-stop`
   - Start with correct version: `make docker-start ES_VERSION=9.1.0`
   - For 9.x branch development, ES 9.x is required

### GPG/Network Issues
If you encounter GPG keyserver or network connectivity issues:

1. **GPG Key Download Failures**: These are typically non-critical warnings during tool installation
2. **Behind Corporate Firewall**: Configure Docker to use appropriate proxy settings
3. **Network Timeouts**: Retry the command or use a different network connection

### Test Environment Issues
- **Unit Tests Only**: Run `make docker-run-phpunit PHPUNIT_OPTIONS="--group=unit"` to skip functional tests that require ES connectivity
- **Memory Issues**: Ensure Docker has sufficient memory allocated (at least 4GB recommended)
- **Port Conflicts**: Check that ports 9200 and 9300 are not in use by other applications

## Coding

### Rules

* Pull requests target the default branch (currently `9.x`). Changes are
  never pushed directly to the default branch.
* We use the
  [forking workflow](https://www.atlassian.com/git/tutorials/comparing-workflows/forking-workflow).
* Use a feature branch for every pull request. Don't open a pull request
  from your fork's default branch.
* All classes use `declare(strict_types=1);`.
* Properties, parameters and return types must be type-declared. Use
  PHPDoc only when it adds information beyond the declared types
  (generics, array shapes, etc.).
* Classes that are not designed to be extended should be marked `final`.
* Static analysis (`make docker-run-phpstan`) and coding-style checks
  (`make docker-run-phpcs`) must pass.

### Pull Requests

* **One change per pull request**. Keep PRs small; if a change is large,
  split it into incremental PRs to make review tractable.
* **Commit messages**: write meaningful commit messages that explain
  *why* the change is needed. Conventional Commits (`fix:`, `feat:`,
  `chore:`, `refactor:`, `docs:`) are preferred but not required.
* **Tests**: every change must be covered by tests. Use
  `#[PHPUnit\Framework\Attributes\Group('unit')]`,
  `#[Group('functional')]`, or `#[Group('benchmark')]` attributes (the
  bootstrap enforces exactly one group per test). Functional tests
  require a running Elasticsearch container.
* **Changelog**: add an entry under the `[Unreleased]` section in
  `CHANGELOG.md` for any user-visible change.
* **Backward-compatibility breaks**: if you must break BC, document the
  rationale in the PR description and list the change under the
  `Backward Compatibility Breaks` heading in `CHANGELOG.md`.
* **Merging**: only maintainers merge pull requests; contributors should
  not merge their own PRs.

### Namespaces & classes

Most namespaces and classes are self-explanatory; look for existing
classes in the same namespace as a template.

* **`Util`** holds static helpers that don't depend on the rest of the
  library (date conversion, escaping, etc.).
* **`Exception`** holds the exception hierarchy. Concrete leaves are
  `final`; intermediate classes used as catch-targets remain open.
* **`Query`, `Aggregation`, `Suggest`** mirror Elasticsearch's request
  taxonomy, with shared base classes such as `AbstractQuery`.
