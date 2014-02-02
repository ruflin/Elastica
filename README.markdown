Elastica: elasticsearch PHP Client
==================================
[![Build Status](https://secure.travis-ci.org/ruflin/Elastica.png?branch=master)](http://travis-ci.org/ruflin/Elastica)
[![Coverage Status](https://coveralls.io/repos/ruflin/Elastica/badge.png)](https://coveralls.io/r/ruflin/Elastica)
[![Dependency Status](https://www.versioneye.com/php/ruflin:Elastica/master/badge.png)](https://www.versioneye.com/php/ruflin:elastica/)
[![Latest Stable Version](https://poser.pugx.org/ruflin/Elastica/v/stable.png)](https://packagist.org/packages/ruflin/elastica)
[![Total Downloads](https://poser.pugx.org/ruflin/Elastica/downloads.png)](https://packagist.org/packages/ruflin/elastica)


Current stable version: [v0.90.10.0](https://github.com/ruflin/Elastica/tree/v0.90.10.0)

Dependencies
------------
|Project|Version|Required|
|-------|-------|--------|
|[Elasticsearch](https://github.com/elasticsearch/elasticsearch/tree/v0.90.10)|0.90.10|yes|
|[Elasticsearch mapper attachments plugin](https://github.com/elasticsearch/elasticsearch-mapper-attachments/tree/v1.9.0)|1.9.0|no|
|[Elasticsearch thrift transport plugin](https://github.com/elasticsearch/elasticsearch-transport-thrift/tree/v1.7.0)|1.7.0|no|
|[Elasticsearch geocluster facet plugin](https://github.com/zenobase/geocluster-facet/tree/0.0.9)|0.0.9|no|


Documentation
---------------------
Check out the [Elastica documentation](http://Elastica.io/) to find out how Elastica works. If you have questions, don't hesitate to ask them in the [Elastica google group](https://groups.google.com/group/elastica-php-client). Issues should go to the [issue tracker from github](https://github.com/ruflin/Elastica/issues).

About
---------------------
PHP client for the distributed search engine [elasticsearch](http://www.elasticsearch.org/) which is
based on [Lucene](http://lucene.apache.org/java/docs/index.html) and can be an alternative to [solr](http://lucene.apache.org/solr/).
The client naming and structure is consistent with [Zend Framework](http://framework.zend.com/)
and other PHP frameworks. This makes it easy to use the client in combination with Zend Framework.

Changes
-------
For changes in the API please check the file [changes.txt](https://github.com/ruflin/Elastica/blob/master/changes.txt)

Versions
--------
The version numbers are consistent with elasticsearch. The version number 0.16.0.0 means it is the first release for elasticsearch version 0.16.0. The next release is called 0.16.0.1. As soon as the elasticsearch is updated and the client is updated, also the next version is called 0.16.1.0. Like this it should be always clear to which versions the Elastica client is compatible.

Compatibility
-------------
Elastica is tested with PHP 5.3.3 and later. Versions prior and equal to v0.19.8.0 are compatible with PHP 5.2

File indexing
-------------
File upload is supported but the mapper attachement plugin has to be installed

    ./bin/plugin -install elasticsearch/elasticsearch-mapper-attachments/1.9.0

Contributing
------------
Help is very welcomed, but code contributions must be done in respect of [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md).

See [Coding guidelines](https://github.com/ruflin/Elastica/wiki/Coding-guidelines) for tips on how to do so.
