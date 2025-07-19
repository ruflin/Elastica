# Custom instructions for GitHub Copilot

This file contains custom instructions for GitHub Copilot. It is used to provide guidance to Copilot on how to behave when it is used in this repository.

## General instructions

- This is an open-source project. All contributions are welcome.
- The project is written in PHP.
- The project uses PHPUnit for testing.
- The project uses php-cs-fixer for code style.
- The project uses phpstan for static analysis.
- The project uses a Makefile for common tasks.
- The project uses composer for dependencies
- The code is compatible with Elasticsearch 9.0 or newer
- Each code change has an entry in the `CHANGELOG.md` file. Exceptions are changes to tests.


## PHP instructions

- All code must be compatible with PHP 8.1 or higher.
- All code must be PSR-2 compliant.
- All classes must be in the `Elastica` namespace.
- All classes must be final, unless they are meant to be extended.
- All methods must have a return type.
- All properties must have a type.
- All parameters must have a type.
- All functions must have a docblock.
- All classes must have a docblock.
- All properties must have a docblock.
- All methods must have a docblock.
- For all methods that call an Elasticsearch API, a link to the Elasticsearch API docs must exist in the documentation.

## Testing instructions

- All code must be tested with PHPUnit.
- All tests must be in the `tests` directory.
- All tests must be in the `Elastica\Test` namespace.
- All test classes must extend `Elastica\Test\Base`.
- All test methods must start with `test`.
- All test methods must have a `@covers` annotation.
- All test methods must have a `@group` annotation.
- For all methods, a `@group unit` and `@group functional` should exist
