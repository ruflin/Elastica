<?php

require_once dirname(__FILE__) . '/../../bootstrap.php';

class Elastica_IndexTest extends Elastica_Test
{
    public function testMapping()
    {
        $index = $this->_createIndex();
        $doc = new Elastica_Document(1, array('id' => 1, 'email' => 'test@test.com', 'username' => 'hanswurst', 'test' => array('2', '3', '5')));

        $type = $index->getType('test');

        $mapping = array('id' => array('type' => 'integer', 'store' => 'yes'), 'email' => array('type' => 'string', 'store' => 'no'),
            'username' => array('type' => 'string', 'store' => 'no'), 'test' => array('type' => 'integer', 'store' => 'no'),);
        $type->setMapping($mapping);

        $type->addDocument($doc);
        $index->optimize();

        $storedMapping = $type->getMapping();

        $this->assertEquals($storedMapping['test']['properties']['id']['type'], 'integer');
        $this->assertEquals($storedMapping['test']['properties']['id']['store'], 'yes');
        $this->assertEquals($storedMapping['test']['properties']['email']['type'], 'string');
        $this->assertEquals($storedMapping['test']['properties']['username']['type'], 'string');
        $this->assertEquals($storedMapping['test']['properties']['test']['type'], 'integer');

        $result = $type->search('hanswurst');
    }

    public function testParent()
    {
        $index = $this->_createIndex();

        $typeBlog = new Elastica_Type($index, 'blog');

        $typeComment = new Elastica_Type($index, 'comment');

        $mapping = new Elastica_Type_Mapping();
        $mapping->setParam('_parent', array('type' => 'blog'));
        $typeComment->setMapping($mapping);

        $entry1 = new Elastica_Document(1);
        $entry1->add('title', 'Hello world');
        $typeBlog->addDocument($entry1);

        $entry2 = new Elastica_Document(2);
        $entry2->add('title', 'Foo bar');
        $typeBlog->addDocument($entry2);

        $entry3 = new Elastica_Document(3);
        $entry3->add('title', 'Till dawn');
        $typeBlog->addDocument($entry3);

        $comment = new Elastica_Document(1);
        $comment->add('author', 'Max');
        $comment->setParent(2); // Entry Foo bar
        $typeComment->addDocument($comment);

        $index->optimize();

        $query = new Elastica_Query_HasChild('Max', 'comment');
        $resultSet = $typeBlog->search($query);
        $this->assertEquals(1, $resultSet->count());
        $this->assertEquals(array('title' => 'Foo bar'), $resultSet->current()->getData());
    }

    public function testAddPdfFile()
    {
        $indexMapping = array('file' => array('type' => 'attachment', 'store' => 'no'), 'text' => array('type' => 'string', 'store' => 'no'),);

        $indexParams = array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0),);

        $index = $this->_createIndex();
        $type = new Elastica_Type($index, 'test');

        $index->create($indexParams, true);
        $type->setMapping($indexMapping);

        $doc1 = new Elastica_Document(1);
        $doc1->addFile('file', BASE_PATH . '/data/test.pdf');
        $doc1->add('text', 'basel world');
        $type->addDocument($doc1);

        $doc2 = new Elastica_Document(2);
        $doc2->add('text', 'running in basel');
        $type->addDocument($doc2);

        $index->optimize();

        $resultSet = $type->search('xodoa');
        $this->assertEquals(1, $resultSet->count());

        $resultSet = $type->search('basel');
        $this->assertEquals(2, $resultSet->count());

        // Author is ruflin
        $resultSet = $type->search('ruflin');
        $this->assertEquals(1, $resultSet->count());

        // String does not exist in file
        $resultSet = $type->search('guschti');
        $this->assertEquals(0, $resultSet->count());
    }

    public function testAddPdfFileContent()
    {
        $indexMapping = array('file' => array('type' => 'attachment', 'store' => 'no'), 'text' => array('type' => 'string', 'store' => 'no'),);

        $indexParams = array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0),);

        $index = $this->_createIndex();
        $type = new Elastica_Type($index, 'test');

        $index->create($indexParams, true);
        $type->setMapping($indexMapping);

        $doc1 = new Elastica_Document(1);
        $doc1->addFileContent('file', file_get_contents(BASE_PATH . '/data/test.pdf'));
        $doc1->add('text', 'basel world');
        $type->addDocument($doc1);

        $doc2 = new Elastica_Document(2);
        $doc2->add('text', 'running in basel');
        $type->addDocument($doc2);

        $index->optimize();

        $resultSet = $type->search('xodoa');
        $this->assertEquals(1, $resultSet->count());

        $resultSet = $type->search('basel');
        $this->assertEquals(2, $resultSet->count());

        // Author is ruflin
        $resultSet = $type->search('ruflin');
        $this->assertEquals(1, $resultSet->count());

        // String does not exist in file
        $resultSet = $type->search('guschti');
        $this->assertEquals(0, $resultSet->count());
    }

    public function testAddWordxFile()
    {
        $indexMapping = array('file' => array('type' => 'attachment'), 'text' => array('type' => 'string', 'store' => 'no'),);

        $indexParams = array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0),);

        $index = $this->_createIndex();
        $type = new Elastica_Type($index, 'content');

        $index->create($indexParams, true);
        $type->setMapping($indexMapping);

        $doc1 = new Elastica_Document(1);
        $doc1->addFile('file', BASE_PATH . '/data/test.docx');
        $doc1->add('text', 'basel world');
        $type->addDocument($doc1);

        $doc2 = new Elastica_Document(2);
        $doc2->add('text', 'running in basel');
        $type->addDocument($doc2);

        $index->optimize();

        $resultSet = $type->search('xodoa');
        $this->assertEquals(1, $resultSet->count());

        $resultSet = $type->search('basel');
        $this->assertEquals(2, $resultSet->count());

        $resultSet = $type->search('ruflin');
        $this->assertEquals(0, $resultSet->count());
    }

    public function testExcludeFileSource()
    {
        $indexMapping = array('file' => array('type' => 'attachment', 'store' => 'yes'), 'text' => array('type' => 'string', 'store' => 'yes'),
            'title' => array('type' => 'string', 'store' => 'yes'),);

        $indexParams = array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0),);

        $index = $this->_createIndex();
        $type = new Elastica_Type($index, 'content');

        $mapping = Elastica_Type_Mapping::create($indexMapping);
        $mapping->setSource(array('excludes' => array('file')));

        $mapping->setType($type);

        $index->create($indexParams, true);
        $type->setMapping($mapping);

        $docId = 1;
        $text = 'Basel World';
        $title = 'No Title';

        $doc1 = new Elastica_Document($docId);
        $doc1->addFile('file', BASE_PATH . '/data/test.docx');
        $doc1->add('text', $text);
        $doc1->add('title', $title);
        $type->addDocument($doc1);

        // Optimization necessary, as otherwise source still in realtime get
        $index->optimize();

        $data = $type->getDocument($docId)->getData();
        $this->assertEquals($data['title'], $title);
        $this->assertEquals($data['text'], $text);
        $this->assertFalse(isset($data['file']));
    }

    /**
     * @expectedException Elastica_Exception_Response
     */
    public function testAddRemoveAlias()
    {
        $client = new Elastica_Client();

        $indexName1 = 'test1';
        $aliasName = 'test-alias';
        $typeName = 'test';

        $index = $client->getIndex($indexName1);
        $index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0)), true);

        $doc = new Elastica_Document(1, array('id' => 1, 'email' => 'test@test.com', 'username' => 'ruflin'));

        $type = $index->getType($typeName);
        $type->addDocument($doc);
        $index->refresh();

        $resultSet = $type->search('ruflin');

        $this->assertEquals(1, $resultSet->count());

        $data = $index->addAlias($aliasName, true)->getData();
        $this->assertTrue($data['ok']);

        $index2 = $client->getIndex($aliasName);
        $type2 = $index2->getType($typeName);

        $resultSet2 = $type2->search('ruflin');
        $this->assertEquals(1, $resultSet2->count());

        $response = $index->removeAlias($aliasName)->getData();
        $this->assertTrue($response['ok']);

        $client->getIndex($aliasName)->getType($typeName)->search('ruflin');
    }

    public function testDeleteIndexDeleteAlias()
    {
        $indexName = 'test';
        $aliasName = 'test-aliase';

        $client = new Elastica_Client();
        $index = $client->getIndex($indexName);

        $index->create(array(), true);
        $index->addAlias($aliasName);

        $status = new Elastica_Status($client);
        $this->assertTrue($status->indexExists($indexName));
        $this->assertTrue($status->aliasExists($aliasName));

        // Deleting index should also remove alias
        $index->delete();

        $status->refresh();
        $this->assertFalse($status->indexExists($indexName));
        $this->assertFalse($status->aliasExists($aliasName));
    }

    public function testAddAliasTwoIndices()
    {
        $indexName1 = 'test1';
        $indexName2 = 'test2';
        $aliasName = 'test-alias';

        $client = new Elastica_Client();
        $index1 = $client->getIndex($indexName1);
        $index2 = $client->getIndex($indexName2);

        $index1->create(array(), true);
        $index1->addAlias($aliasName);
        $index2->create(array(), true);

        $status = new Elastica_Status($client);
        $this->assertTrue($status->indexExists($indexName1));
        $this->assertTrue($status->indexExists($indexName2));
        $this->assertTrue($status->aliasExists($aliasName));
        $this->assertTrue($index1->getStatus()->hasAlias($aliasName));
        $this->assertFalse($index2->getStatus()->hasAlias($aliasName));

        $index2->addAlias($aliasName);
        $this->assertTrue($index1->getStatus()->hasAlias($aliasName));
        $this->assertTrue($index2->getStatus()->hasAlias($aliasName));
    }

    public function testReplaceAlias()
    {
        $indexName1 = 'test1';
        $indexName2 = 'test2';
        $aliasName = 'test-alias';

        $client = new Elastica_Client();
        $index1 = $client->getIndex($indexName1);
        $index2 = $client->getIndex($indexName2);

        $index1->create(array(), true);
        $index1->addAlias($aliasName);
        $index2->create(array(), true);

        $status = new Elastica_Status($client);
        $this->assertTrue($status->indexExists($indexName1));
        $this->assertTrue($status->indexExists($indexName2));
        $this->assertTrue($status->aliasExists($aliasName));
        $this->assertTrue($index1->getStatus()->hasAlias($aliasName));
        $this->assertFalse($index2->getStatus()->hasAlias($aliasName));

        $index2->addAlias($aliasName, true);
        $this->assertFalse($index1->getStatus()->hasAlias($aliasName));
        $this->assertTrue($index2->getStatus()->hasAlias($aliasName));
    }

    public function testAddDocumentVersion()
    {
        $client = new Elastica_Client();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = new Elastica_Type($index, 'test');

        $doc1 = new Elastica_Document(1);
        $doc1->add('title', 'Hello world');

        $return = $type->addDocument($doc1);
        $data = $return->getData();
        $this->assertEquals(1, $data['_version']);

        $return = $type->addDocument($doc1);
        $data = $return->getData();
        $this->assertEquals(2, $data['_version']);
    }

    public function testClearCache()
    {
        $client = new Elastica_Client();
        $index1 = $client->getIndex('test1');

        $response = $index1->clearCache();
        $this->assertFalse($response->hasError());
    }

    public function testFlush()
    {
        $client = new Elastica_Client();
        $index = $client->getIndex('test1');

        $response = $index->flush();
        $this->assertFalse($response->hasError());
    }

    public function testExists()
    {
        $index = $this->_createIndex();

        $this->assertTrue($index->exists());

        $index->delete();

        $this->assertFalse($index->exists());
    }

    /**
     * Test $index->delete() return value for unknown index
     *
     * Tests if deleting an index that does not exist in Elasticsearch,
     * correctly returns a boolean true from the hasError() method of
     * the Elastica_Response object
     */
    public function testDeleteMissingIndexHasError()
    {
        $client = new Elastica_Client();
        $index = $client->getIndex('index_does_not_exist');

        try {
            $index->delete();
            $this->fail('This should never be reached. Deleting an unknown index will throw an exception');
        } catch (Elastica_Exception_Response $error) {
            $response = $error->getResponse();
            $this->assertTrue($response->hasError());
        }
    }

    /**
     * Tests to see if the test type mapping exists when calling $index->getMapping()
     */
    public function testIndexGetMapping()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $mapping = array('id' => array('type' => 'integer', 'store' => 'yes'), 'email' => array('type' => 'string', 'store' => 'no'),
            'username' => array('type' => 'string', 'store' => 'no'), 'test' => array('type' => 'integer', 'store' => 'no'),);

        $type->setMapping($mapping);
        $indexMappings = $index->getMapping();

        $this->assertEquals($indexMappings['elastica_test']['test']['properties']['id']['type'], 'integer');
        $this->assertEquals($indexMappings['elastica_test']['test']['properties']['id']['store'], 'yes');
        $this->assertEquals($indexMappings['elastica_test']['test']['properties']['email']['type'], 'string');
        $this->assertEquals($indexMappings['elastica_test']['test']['properties']['username']['type'], 'string');
        $this->assertEquals($indexMappings['elastica_test']['test']['properties']['test']['type'], 'integer');
    }

    /**
     * Tests to see if the index is empty when there are no types set.
     */
    public function testEmptyIndexGetMapping()
    {
        $index = $this->_createIndex();
        $indexMappings = $index->getMapping();

        $this->assertTrue(empty($indexMappings['elastica_test']));
    }

    /**
     * Test to see if search Default Limit works
     */
    public function testLimitDefaultIndex()
    {
        $client = new Elastica_Client();
        $index = $client->getIndex('zero');
        $index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0)), true);

        $docs = array();

        $docs[] = new Elastica_Document(1, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(2, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(3, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(4, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(5, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(6, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(7, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(8, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(9, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(10, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(11, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));

        $type = $index->getType('zeroType');
        $type->addDocuments($docs);
        $index->refresh();

        // default limit results  (default limit is 10)
        $resultSet = $index->search('farrelley');
        $this->assertEquals(10, $resultSet->count());

        // limit = 1
        $resultSet = $index->search('farrelley', 1);
        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * @expectedException Elastica_Exception_Invalid
     */
    public function testCreateArray()
    {
        $client = new Elastica_Client();
        $indexName = 'test';

        //Testing recreate (backward compatibility)
        $index = $client->getIndex($indexName);
        $index->create(array(), true);
        $status = new Elastica_Status($client);
        $this->assertTrue($status->indexExists($indexName));

        //Testing create index with array options
        $opts = array('recreate' => true, 'routing' => 'r1,r2');
        $index->create(array(), $opts);
        $status = new Elastica_Status($client);
        $this->assertTrue($status->indexExists($indexName));

        //Testing invalid options
        $opts = array('recreate' => true, 'routing' => 'r1,r2', 'testing_invalid_option' => true);
        $index->create(array(), $opts);
        $status = new Elastica_Status($client);
        $this->assertTrue($status->indexExists($indexName));
    }
}
