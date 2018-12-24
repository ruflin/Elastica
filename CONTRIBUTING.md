Contributing
============
Help is very welcomed. Code contributions must be done in respect of [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md).
More details on how to contribute and guidelines for [pull requests](http://elastica.io/contribute/pull-request.html) can be found [here](http://elastica.io/contribute/).

See [Coding guidelines](http://elastica.io/contribute/coding-guidelines.html) for tips on how to do so.
All changes must be documented in the [CHANGELOG.md](https://github.com/ruflin/Elastica/blob/master/CHANGELOG.md).


Issues
------
* Bugs & Feature requests: If you found a bug, open an issue on [Github](https://github.com/ruflin/Elastica/issues). Please search for already reported similary issues before opening a new one. You should include code examples (with dependencies if needed) and your Elastica version to the issue description.
* Questions & Problems: If you have questions or a specific problem, use the [Elastica Gitter](https://gitter.im/ruflin/Elastica) Chat or open an issue on [Stackoverflow](http://stackoverflow.com/questions/tagged/elastica). Make sure to assign the tag Elastica.


Setup
-----
Elastica uses docker for it's development environment. To use the docker environment, check out the Makefile for the necessary commands.
* Run your changes / tests in the virtual environment to make sure it is reproducible.
* Run the tests before creating the pull request using docker-compose locally.

Commands
--------
To run the commands below, you must have docker-compose [installed](https://docs.docker.com/compose/install/). The first time the commands are run it takes some time to download all the partial images. From then on the commands should run very fast. The advantage in using the commands below is that no local tools and libraries have to be installed and it is guaranteed that everyone is using the same tools.

## Run Tests

To run all tests inside the docker environment, run the following command:

```
make tests
```

If you want to run just a specific test or a one specific file, run the following command by replacing your file with the existing path:

```
make test TEST="test/lib/Elastica/Test/SearchTest.php"
```

## Check style of your code
This command will call php-cs-fixer (2.x) with the predefined settings for the elastica project.
You need to execute it before open a Pull Request; Travis build will do check for style if they
are not respected **build will fail**. 
No local setup of the tools is needed as everything will happen directly in the container.

```
make lint
```

and

```
make check-style
```
will do a check executing php-cs-fixer in ```dry-run``` mode. It is usually executed during Travis build.

  Exit codes
  ----------

  Exit code is built using following bit flags:

  *  0 OK.
  *  1 General error (or PHP minimal requirement not matched).
  *  4 Some files have invalid syntax (only in dry-run mode).
  *  8 Some files need fixing (only in dry-run mode).
  * 16 Configuration error of the application.
  * 32 Configuration error of a Fixer.
  * 64 Exception raised within the application.

Coding
------

### Rules
* Pull requests are made to master. Changes are never pushed directly (without pull request) into master.
* We use the Forking Workflow. https://www.atlassian.com/git/tutorials/comparing-workflows/forking-workflow
* Follow the coding guidelines.
* Use a feature branch for every pull request. Don't open a pull request from your master branch.

### Pull Requests
* One change per pull requests: Keep your pull requests as small as possible. Only one change should happen per pull request. This makes it easier to review and provided feedback. If you have a large pull request, try to split it up in multiple smaller requests.
* Commit messages: Make sure that your commit messages have meaning and provide an understanding on what was changed without looking at the code.
* Pull requests should be opened as early as possible as pull requests are also here for communication / discussing changes. Add a comment when your pull request is ready to be merged.
* Tests: Your addition / change must be tested and the builds must be green. Test your changes locally. Add unit tests and if possible functional tests. Don't forget to add the group to your tests. The 4 available groups are @functional, @unit, @shutdown, @benchmark
* Update the CHANGELOG.md file with your changes
* Backward Compatibility breaks: In case you break backward compatibility, provide details on why this is needed.
* Merge: No one should ever merge his own pull request


### Name Spaces & Classes
Most name spaces and classes are self explanotary and use cases can be taken from classes which already exist.

#### Tool Namespace
The namespace Tool is used for making more complex functionality of Elastica available to the users. In general it maps existing functionality of Elastica and offers simplified functions.

#### Util Class
The util class is used for all static functions which are used in the Elastica library but don't access the library itself.
