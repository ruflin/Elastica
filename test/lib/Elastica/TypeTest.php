<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

class Elastica_TypeTest extends Elastica_Test
{
    public function testSearch()
    {
        $index = $this->_createIndex();

        $type = new Elastica_Type($index, 'user');

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
        $index->refresh();

        $resultSet = $type->search('rolf');
        $this->assertEquals(1, $resultSet->count());

        // Test if source is returned
        $result = $resultSet->current();
        $this->assertEquals(3, $result->getId());
        $data = $result->getData();
        $this->assertEquals('rolf', $data['username']);
    }

    public function testNoSource()
    {
        $index = $this->_createIndex();

        $type = new Elastica_Type($index, 'user');
        $mapping = new Elastica_Type_Mapping($type, array(
                'id' => array('type' => 'integer', 'store' => 'yes'),
                'username' => array('type' => 'string', 'store' => 'no'),
            ));
        $mapping->setSource(array('enabled' => false));
        $type->setMapping($mapping);

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

        // To update index
        $index->refresh();

        $resultSet = $type->search('rolf');

        $this->assertEquals(1, $resultSet->count());

        // Tests if no source is in response except id
        $result = $resultSet->current();
        $this->assertEquals(3, $result->getId());
        $this->assertEmpty($result->getData());
    }

    public function testDeleteDocument()
    {
        $index = $this->_createIndex();
        $type = new Elastica_Type($index, 'user');

        // Adds hans, john and rolf to the index
        $docs = array(
            new Elastica_Document(1, array('username' => 'hans', 'test' => array('2', '3', '5'))),
            new Elastica_Document(2, array('username' => 'john', 'test' => array('1', '3', '6'))),
            new Elastica_Document(3, array('username' => 'rolf', 'test' => array('2', '3', '7'))),
        );
        $type->addDocuments($docs);
        $index->refresh();

        // sanity check for rolf
        $resultSet = $type->search('rolf');
        $this->assertEquals(1, $resultSet->count());
        $data = $resultSet->current()->getData();
        $this->assertEquals('rolf', $data['username']);

        // delete rolf
        $type->deleteById(3);
        $index->refresh();

        // rolf should no longer be there
        $resultSet = $type->search('rolf');
        $this->assertEquals(0, $resultSet->count());

        // it should not be possible to delete the entire type with this method
        try {
            $type->deleteById(' ');
        } catch (Exception $e) {
            /* ignore */
        }

        try {
            $type->deleteById(null);
        } catch (Exception $e) {
            /* ignore */
        }

        try {
            $type->deleteById(array());
        } catch (Exception $e) {
            /* ignore */
        }

        try {
            $type->deleteById('*');
        } catch (Exception $e) {
            /* ignore */
        }

        try {
            $type->deleteById('*:*');
        } catch (Exception $e) {
            /* ignore */
        }

        try {
            $type->deleteById('!');
        } catch (Exception $e) {
            /* ignore */
        }

        $index->refresh();

        // rolf should no longer be there
        $resultSet = $type->search('john');
        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * @expectedException Elastica_Exception_NotFound
     */
    public function testGetDocumentNotExist()
    {
        $index = $this->_createIndex();
        $type = new Elastica_Type($index, 'test');
        $type->addDocument(new Elastica_Document(1, array('name' => 'ruflin')));
        $index->refresh();

        $type->getDocument(1);

        $type->getDocument(2);
    }

    public function testDeleteByQuery()
    {
        $index = $this->_createIndex();
        $type = new Elastica_Type($index, 'test');
        $type->addDocument(new Elastica_Document(1, array('name' => 'ruflin nicolas')));
        $type->addDocument(new Elastica_Document(2, array('name' => 'ruflin')));
        $index->refresh();

        $response = $index->search('ruflin*');
        $this->assertEquals(2, $response->count());

        $response = $index->search('nicolas');
        $this->assertEquals(1, $response->count());

        // Delete first document
        $response = $type->deleteByQuery('nicolas');
        $this->assertTrue($response->isOk());

        $index->refresh();

        // Makes sure, document is deleted
        $response = $index->search('ruflin*');
        $this->assertEquals(1, $response->count());

        $response = $index->search('nicolas');
        $this->assertEquals(0, $response->count());
    }

    /**
     * Test to see if search Default Limit works
     */
    public function testLimitDefaultType()
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

        // default results  (limit default is 10)
        $resultSet = $type->search('farrelley');
        $this->assertEquals(10, $resultSet->count());

        // limit = 1
        $resultSet = $type->search('farrelley', 1);
        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * Test Delete of index type.  After delete will check for type mapping.
     */
    public function testDeleteType()
    {
        $index = $this->_createIndex();
        $type = new Elastica_Type($index, 'test');
        $type->addDocument(new Elastica_Document(1, array('name' => 'ruflin nicolas')));
        $type->addDocument(new Elastica_Document(2, array('name' => 'ruflin')));
        $index->refresh();

        $type->delete();
        try {
            $type->getMapping();
        } catch (Elastica_Exception_Response $expected) {
            $this->assertEquals("TypeMissingException[[elastica_test] type[test] missing]", $expected->getMessage());

            return;
        }

        $this->fail('Mapping for type[test] in [elastica_test] still exists');
    }

    public function testMoreLikeThisApi()
    {
        $client = new Elastica_Client(array('persistent' => false));
        $index = $client->getIndex('elastica_test');
        $index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0)), true);

        $type = new Elastica_Type($index, 'mlt_test');
        $type->addDocument(new Elastica_Document(1, array('visible' => true, 'name' => 'bruce wayne batman')));
        $type->addDocument(new Elastica_Document(2, array('visible' => true, 'name' => 'bruce wayne')));
        $type->addDocument(new Elastica_Document(3, array('visible' => false, 'name' => 'bruce wayne')));
        $type->addDocument(new Elastica_Document(4, array('visible' => true, 'name' => 'batman')));
        $type->addDocument(new Elastica_Document(5, array('visible' => false, 'name' => 'batman')));
        $type->addDocument(new Elastica_Document(6, array('visible' => true, 'name' => 'superman')));
        $type->addDocument(new Elastica_Document(7, array('visible' => true, 'name' => 'spiderman')));

        $index->refresh();

        $document = $type->getDocument(1);

        // Return all similar
        $resultSet = $type->moreLikeThis($document, array('min_term_freq' => '1', 'min_doc_freq' => '1'));
        $this->assertEquals(4, $resultSet->count());

        // Return just the visible similar
        $query 				= new Elastica_Query();
        $filterTerm 		= new Elastica_Filter_Term();
        $filterTerm->setTerm('visible', true);
        $query->setFilter($filterTerm);

        $resultSet = $type->moreLikeThis($document, array('min_term_freq' => '1', 'min_doc_freq' => '1'), $query);
        $this->assertEquals(2, $resultSet->count());
    }

    public function testUpdateDocument()
    {
        $client = new Elastica_Client();
        $index = $client->getIndex('elastica_test');
        $type = $index->getType('update_type');
        $id = 1;
        $type->addDocument(new Elastica_Document($id, array('name' => 'bruce wayne batman')));
        $newName = 'batman';
        $update = new Elastica_Script("ctx._source.name = name", array('name' => $newName));
        $type->updateDocument($id, $update, array('refresh' => true));
        $updatedDoc = $type->getDocument($id)->getData();
        $this->assertEquals($newName, $updatedDoc['name'], "Name was not updated");
    }
}
