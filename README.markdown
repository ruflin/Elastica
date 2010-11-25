Elastica: elasticsearch PHP Client
==================================


PHP client for the distributed search engine [elasticsearch](http://www.elasticsearch.com/) which is 
based on [Lucene](http://lucene.apache.org/java/docs/index.html) and can be an alternative to [solr](http://lucene.apache.org/solr/).

This client is similar to [nervetattoo elasticsearch PHP client](http://github.com/nervetattoo/elasticsearch) (also a lot of ideas are borrowed from the client),
but the naming is consistent with [Zend Framework](http://framework.zend.com/)
and other PHP frameworks. This makes it easy to use the client in combination with Zend Framework.

With this client I try to model the elasticsearch REST API in an object oriented way which also makes it possible to extend the client and add new types of queries,
filters, facets, transport layers and more.

The client uses the [REST API](http://www.elasticsearch.com/docs/elasticsearch/rest_api/) from elasticsearch and tries to
 provide a simple way of accessing the elasticsearch functionality.
Arguments are passed as arrays which are automatically encoded to JSON which makes it possible to also use not implemented features of the request methods.



Examples
--------

Lots of basic examples can also be found in the test classes.

	// Creates a new index 'xodoa' and a type 'user' inside this index
	$client = new Elastica_Client();    
	$index = $client->getIndex('xodoa');
	$index->create(array(), true);

	$type = $index->getType('user');


	// Adds 1 document to the index
	$doc1 = new Elastica_Document(1, 
		array('username' => 'hans', 'test' => array('2', '3', '5'))
	);
	$type->addDocument($doc1);

	// Adds a list of documents with _bulk upload to the index
	$docs = array();
	$docs[] = new Elastica_Document(2, 
		array('username' => 'john', 'test' => array('1', '3', '6'))
	);
	$docs[] = new Elastica_Document(3, 
		array('username' => 'rolf', 'test' => array('2', '3', '7'))
	);
	$type->addDocuments($docs);

	// Index needs a moment to be updated
	$index->refresh();

	$resultSet = $type->search('rolf');


The client is still under heavy development. Feedback is appreciated.

The basic object structure is as following:
Client -> Index -> Type -> Document

Compatibility
-------------
At the moment the client should be backward compatible to PHP 5.2. That's the reason why 
the client was not directly built with namespaces and other nice PHP 5.3 features.

In general the client works with elasticsearch version 0.11.0 and 0.12.0. Bulk upload only works with version 0.12.0

File indexing
-------------
File upload is supported but the mapper attachement plugin has to be installed

	./bin/plugin install mapper-attachments

Credits
-------
The basic ideas for this client are from nervetattoo (Raymond Julin) who already created a client for elasticsearch in PHP that can be found here:
http://github.com/nervetattoo/elasticsearch