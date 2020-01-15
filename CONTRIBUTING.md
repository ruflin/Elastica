# Contributing
Help is very welcomed. Code contributions must be done in respect of [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md).
More details on how to contribute and guidelines for [pull requests](http://elastica.io/contribute/pull-request.html) can be found [here](http://elastica.io/contribute/).

See [Coding guidelines](http://elastica.io/contribute/coding-guidelines.html) for tips on how to do so.
All changes must be documented in the [CHANGELOG.md](https://github.com/ruflin/Elastica/blob/master/CHANGELOG.md).

## Issues
* Bugs & Feature requests: If you found a bug, open an issue on [Github](https://github.com/ruflin/Elastica/issues).
    Please search for already reported similar issues before opening a new one.
    You should include code examples (with dependencies if needed) and your Elastica version to the issue description.
* Questions & Problems: If you have questions or a specific problem, use the [Elastica Gitter](https://gitter.im/ruflin/Elastica)
    Chat or open an issue on [Stackoverflow](http://stackoverflow.com/questions/tagged/elastica).
    Make sure to assign the tag Elastica.

## Setup
Elastica uses docker for its development environment.
Make sure you have both `docker` and  [docker-compose](https://docs.docker.com/compose/install/) installed.

This repository comes with a set of docker-compose.yml templates used for the CI and the local development.

To start the local docker instance run `make docker-start`: the command will pull the required containers for PHP and ES.
For ES to properly run, the `vm.max_map_count=262144` System configuration is needed by ES to properly spin up the nodes.

Check out the Makefile for other commands that can be used to run tests and other operations:
* Run your changes / tests in the virtual environment to make sure it is reproducible.
* Run the tests before creating the pull request using docker-compose locally.

### PHP Tools
Elastica uses [phive](https://phar.io/) to manage PHP tools and their installation.
Those tools are available under the `tools/` directory and are installed when need by a command (see below).
 
Some of the installed tools are:
  - `php-cs-fixer.phar`: PHP Coding styles 
  - `phpunit.phar`: PHP unit testing

## Commands
The advantage in using the commands below is that no local tools and libraries have to be installed and it is guaranteed
that everyone is using the same tools.

The tools required for each command will be installed only when needed byt the command itself.

### Coding standards
Run the command `make docker-run-phpcs` to run the coding-standard checks on the code inside the docker container.

You need to execute it before open a Pull Request: the CI will execute the same checks, to make sure that every PR
respects the same coding standards.

The command `make docker-fix-phpcs` can be used to fix the code automatically.

### Tests
Run the command `make docker-run-phpunit` to run the PHP tests on the code inside the docker container.

If you want to run just a specific test or a one specific file, you should run the `tools/phpunit.phar` from inside
the docker container:
    1. enter the docker container `docker exec -ti 'elastica_php' sh`
    2. run a specific test: `tools/phpunit.phar --filter=DocumentTest` to run only tests matching `DocumentTest`

## Coding

### Rules
* Pull requests are made to master.
    Changes are never pushed directly (without pull request) into master.
* We use the Forking Workflow.
    https://www.atlassian.com/git/tutorials/comparing-workflows/forking-workflow
* Follow the coding guidelines.
* Use a feature branch for every pull request.
    Don't open a pull request from your master branch.

### Pull Requests
* One change per pull requests: Keep your pull requests as small as possible.
    Only one change should happen per pull request.
    This makes it easier to review and provided feedback.
    If you have a large pull request, try to split it up in multiple smaller requests.
* Commit messages: Make sure that your commit messages have meaning and provide an understanding on what was changed
    without looking at the code.
* Pull requests should be opened as early as possible as pull requests are also here for communication / discussing changes.
    Add a comment when your pull request is ready to be merged.
* Tests: Your addition / change must be tested and the builds must be green.
    Test your changes locally.
    Add unit tests and if possible functional tests.
    Don't forget to add the group to your tests.
* Update the CHANGELOG.md file with your changes
* Backward Compatibility breaks: In case you break backward compatibility, provide details on why this is needed.
* Merge: No one should ever merge their own pull request

### Name Spaces & Classes
Most name spaces and classes are self explanatory and use cases can be taken from classes which already exist.

#### Tool Namespace
The namespace Tool is used for making more complex functionality of Elastica available to the users.
In general it maps existing functionality of Elastica and offers simplified functions.

#### Util Class
The util class is used for all static functions which are used in the Elastica library but don't access the library itself.
