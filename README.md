# Elastica: elasticsearch PHP Client

[![Latest Stable Version](https://poser.pugx.org/ruflin/Elastica/v/stable)](https://packagist.org/packages/ruflin/elastica)
[![Build Status](https://github.com/ruflin/elastica/actions/workflows/continuous-integration.yaml/badge.svg?branch=9.x)](https://github.com/ruflin/Elastica/actions/workflows/continuous-integration.yaml?query=branch=9.x)
[![codecov.io](https://codecov.io/gh/ruflin/Elastica/branch/9.x/graph/badge.svg)](https://app.codecov.io/github/ruflin/Elastica/tree/9.x)
[![Total Downloads](https://poser.pugx.org/ruflin/Elastica/downloads)](https://packagist.org/packages/ruflin/elastica)
[![Join the chat at https://gitter.im/ruflin/Elastica](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/ruflin/Elastica?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

All documentation for Elastica can be found under [Elastica.io](http://Elastica.io/).
If you have questions, don't hesitate to ask them on [Stack Overflow](http://stackoverflow.com/questions/tagged/elastica)
and add the Tag "Elastica" or in our [Gitter](https://gitter.im/ruflin/Elastica) channel.

All library issues should go to the [issue tracker from GitHub](https://github.com/ruflin/Elastica/issues).

## Compatibility

This release is compatible with all Elasticsearch 9.0 releases and onwards.

The testsuite is run against the most recent minor version of Elasticsearch, currently 9.0.0

## Contributing

Contributions are always welcome.
For details on how to contribute, check the [CONTRIBUTING](https://github.com/ruflin/Elastica/blob/master/CONTRIBUTING.md) file.

## Versions & Dependencies

This project tries to follow Elasticsearch in terms of [End of Life](https://www.elastic.co/support/eol) and maintenance since 5.x.
It is generally recommended to use the latest point release of the relevant branch.

| Elastica branch                                    | ElasticSearch | elasticsearch-php | PHP            |
|----------------------------------------------------|---------------|-------------------|----------------|
| [9.x](https://github.com/ruflin/Elastica/tree/9.x) | 9.x           | ^9.0              | >=8.1 <8.4     |
| [8.x](https://github.com/ruflin/Elastica/tree/8.x) | 8.x           | ^8.4              | >=8.0 <8.4     |
| [7.x](https://github.com/ruflin/Elastica/tree/7.x) | 7.x           | ^7.0              | ^7.2 \|\| ^8.0 |
| [6.x](https://github.com/ruflin/Elastica/tree/6.x) | 6.x           | ^6.0              | ^7.0 \|\| ^8.0 |

Unmaintained versions:

| Elastica version                                   | ElasticSearch | elasticsearch-php | PHP      |
|----------------------------------------------------|---------------|-------------------|----------|
| [5.x](https://github.com/ruflin/Elastica/tree/5.x) | 5.x           | ^5.0              | \>=5.6   |
| [3.x](https://github.com/ruflin/Elastica/tree/3.x) | 2.4.0         | no                | \>=5.4   |
| [2.x](https://github.com/ruflin/Elastica/tree/2.x) | 1.7.2         | no                | \>=5.3.3 |
