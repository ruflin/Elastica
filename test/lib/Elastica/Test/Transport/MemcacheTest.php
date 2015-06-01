<?php
namespace Elastica\Test\Transport;

use Elastica\Document;
use Elastica\Query;
use Elastica\Query\QueryString;
use Elastica\Request;
use Elastica\Test\Base as BaseTest;

class MemcacheTest extends BaseTest
{
    public static function setUpBeforeClass()
    {
        if (!extension_loaded('Memcache')) {
            self::markTestSkipped('pecl/memcache must be installed to run this test case');
        }
    }

    protected function _getMemcacheClient()
    {
        return $this->_getClient(array(
            'host' => $this->_getHost(),
            'port' => 11211,
            'transport' => 'Memcache',
        ));
    }

    /**
     * @group functional
     */
    public function testConstruct()
    {
        $client = $this->_getMemcacheClient();
        $this->assertEquals($this->_getHost(), $client->getConnection()->getHost());
        $this->assertEquals(11211, $client->getConnection()->getPort());
    }

    /**
     * @group functional
     */
    public function testCreateDocument()
    {
        $index = $this->_createIndex();
        $this->_waitForAllocation($index);
        $type = $index->getType('foo');

        // Create document
        $document = new Document(1, array('username' => 'John Doe'));
        $type->addDocument($document);
        $index->refresh();

        // Check it was saved
        $document = $type->getDocument(1);
        $this->assertEquals('John Doe', $document->get('username'));
    }

    /**
     * @group functional
     * @expectedException Elastica\Exception\NotFoundException
     */
    public function testDeleteDocument()
    {
        $index = $this->_createIndex();
        $this->_waitForAllocation($index);
        $type = $index->getType('foo');

        // Create document
        $document = new Document(1, array('username' => 'John Doe'));
        $type->addDocument($document);
        $index->refresh();

        // Delete document
        $type->deleteById(1);

        // Check if document is not exists
        $document = $type->getDocument(1);
    }

    /**
     * @group functional
     */
    public function testUpdateDocument()
    {
        $index = $this->_createIndex();
        $this->_waitForAllocation($index);
        $type = $index->getType('foo');

        // Create document
        $document = new Document(1, array('username' => 'John Doe'));
        $type->addDocument($document);
        $index->refresh();

        // Check it was saved
        $savedDocument = $type->getDocument(1);
        $this->assertEquals('John Doe', $savedDocument->get('username'));

        // Update document
        $newDocument = new Document(1, array('username' => 'Doe John'));
        $type->updateDocument($newDocument);
        $index->refresh();

        // Check it was updated
        $newSavedDocument = $type->getDocument(1);
        $this->assertEquals('Doe John', $newSavedDocument->get('username'));
    }

    /**
     * @group functional
     */
    public function testSearchDocument()
    {
        $index = $this->_createIndex();
        $this->_waitForAllocation($index);
        $type = $index->getType('fruits');

        // Create documents
        $docs = array(
            new Document(1, array('name' => 'banana')),
            new Document(2, array('name' => 'apple')),
            new Document(3, array('name' => 'orange')),
        );
        $type->addDocuments($docs);
        $index->refresh();

        // Search documents
        $queryString = new QueryString('orange');
        $query = new Query($queryString);
        $resultSet = $type->search($query);

        // Check if correct document was found
        $this->assertEquals(1, $resultSet->getTotalHits());
        $this->assertEquals(3, $resultSet[0]->getId());
        $data = $resultSet[0]->getData();
        $this->assertEquals('orange', $data['name']);
    }

    /**
     * @group functional
     * @expectedException Elastica\Exception\InvalidException
     * @expectedExceptionMessage is not supported in memcache transport
     */
    public function testHeadRequest()
    {
        $client = $this->_getMemcacheClient();
        $client->request('foo', Request::HEAD);
    }

    /**
     * @group functional
     * @expectedException Elastica\Exception\InvalidException
     * @expectedExceptionMessage is not supported in memcache transport
     */
    public function testInvalidRequest()
    {
        $client = $this->_getMemcacheClient();
        $client->request('foo', 'its_fail');
    }

    /**
     * @group functional
     * @expectedException Elastica\Exception\Connection\MemcacheException
     * @expectedExceptionMessage is too long
     */
    public function testRequestWithLongPath()
    {
        $client = $this->_getMemcacheClient();
        $index = $client->getIndex('memcache-test');
        $index->create();

        $this->_waitForAllocation($index);

        $queryString = new QueryString(str_repeat('z', 300));
        $query = new Query($queryString);
        $index->search($query);
    }
}
