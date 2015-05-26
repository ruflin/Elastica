Contributing
============
Help is very welcomed. Code contributions must be done in respect of [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md).
More details on how to contribute and guidelines for [pull requests](http://elastica.io/contribute/pull-request.html) can be found [here](http://elastica.io/contribute/).

See [Coding guidelines](http://elastica.io/contribute/coding-guidelines.html) for tips on how to do so.
All changes which are made to the project are added to the [CHANGELOG.md](https://github.com/ruflin/Elastica/blob/master/CHANGELOG.md).


Issues
------
* Bugs & Feature requests: If you found a bug, open an issue on [Github](https://github.com/ruflin/Elastica/issues). Before you open it search the issues which were already reported to make sure the issue doesn't already exist. Make sure to report the issue in a way that it can be reproduced and you report which version of Elastica and its dependencies you are using. Best is to report the problem with a code example.
* Questions & Problems: If you have questions or a specific problem, use the [Elastica Gitter](https://gitter.im/ruflin/Elastica) Chat or open an issue on [Stackoverflow](http://stackoverflow.com/questions/tagged/elastica). Make sure to assign the tag Elastica.


Setup
-----
Elastica currently allows two setups for development. Either through vagrant or docker-compose. To use the vagrant environment, run `vagrant up`. To use the docker environment, check out the Makefile for the necessary commands.
* Run your changes / tests in the virtual environment to make sure it is reproducible. Don't use a local setup
* Run tests locally on your machine first


Coding
------

These are the points to be discussed (just some examples below)

## Rules
* Changes are never pushed into master
* Pull requests are made to master
* We use the Forking Workflow. https://www.atlassian.com/git/tutorials/comparing-workflows/forking-workflow
* Follow the coding guidelines.
* Use a feature branch for every pull request. Don't open a pull request from your master branch.

## Pull Requests
* One change per pull requests: Keep your pull requests as small as possible. Only one change should happen per pull request. This makes it easier to review and provided feedback. If you have a large pull request, try to split it up in multiple smaller requests.
* Commit messages: Make sure that your commit messages have meaning and provide an understanding on what was changed without looking at the code.
* Pull requests should be opened as early as possible as pull requests are also here for communication / discussing changes. Add a comment when your pull request is ready to be merged.
* Tests: Your addition / change must be tested and the builds must be green. Test your changes locally. Add unit tests and if possible functional tests. Don't forget to add the group to your tests.
* Update the CHANGELOG.md file with your changes
* Backward Compatibility breaks: In case you break backward compatibility, provide details on why this is needed.
* Merge: No one should ever merge his own pull request
