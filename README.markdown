Elastica: elasticsearch PHP Client
==================================

[![Build Status](https://secure.travis-ci.org/ruflin/Elastica.png?branch=master)](http://travis-ci.org/ruflin/Elastica)

Documentation
---------------------
Check out the [Elastica documentation](http://ruflin.github.com/Elastica/) to find out how Elastica works. If you have questions, don't hesitate to ask them in the [Elastica google group](https://groups.google.com/group/elastica-php-client). Issues should go to the [issue tracker from github](https://github.com/ruflin/Elastica/issues).

About
---------------------
PHP client for the distributed search engine [elasticsearch](http://www.elasticsearch.com/) which is 
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
At the moment the client should be backward compatible to PHP 5.2. That's the reason why 
the client was not directly built with namespaces and other nice PHP 5.3 features.

File indexing
-------------
File upload is supported but the mapper attachement plugin has to be installed

	./bin/plugin install mapper-attachments

Contributing
------------
Help is very welcomed, but code contributions must be done in respect of [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md).

To facilitate this, you can use [php-cs-fixer](https://github.com/fabpot/PHP-CS-Fixer) by running `php-cs-fixer fix --level=all /path/to/project`.

`--level=all` is used even though it is not part of a standard simply because we like the code to be consistent.
