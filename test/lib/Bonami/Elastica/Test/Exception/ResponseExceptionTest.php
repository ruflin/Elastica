<?php
namespace Elastica\Test\Exception;

use Elastica\Document;
use Elastica\Exception\ResponseException;

class ResponseExceptionTest extends AbstractExceptionTest
{
    /**
     * @group functional
     */
    public function testCreateExistingIndex()
    {
        $this->_createIndex('woo', true);

        try {
            $this->_createIndex('woo', false);
            $this->fail('Index created when it should fail');
        } catch (ResponseException $ex) {
            $this->assertEquals('IndexAlreadyExistsException', $ex->getElasticsearchException()->getExceptionName());
            $this->assertEquals(400, $ex->getElasticsearchException()->getCode());
        }
    }

    /**
     * @group functional
     */
    public function testBadType()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $type->setMapping(array(
            'num' => array(
                'type' => 'long',
            ),
        ));

        try {
            $type->addDocument(new Document('', array(
                'num' => 'not number at all',
            )));
            $this->fail('Indexing with wrong type should fail');
        } catch (ResponseException $ex) {
            $this->assertEquals('MapperParsingException', $ex->getElasticsearchException()->getExceptionName());
            $this->assertEquals(400, $ex->getElasticsearchException()->getCode());
        }
    }

    /**
     * @group functional
     */
    public function testWhatever()
    {
        $index = $this->_createIndex();
        $index->delete();

        try {
            $index->search();
        } catch (ResponseException $ex) {
            $this->assertEquals('IndexMissingException', $ex->getElasticsearchException()->getExceptionName());
            $this->assertEquals(404, $ex->getElasticsearchException()->getCode());
        }
    }
}
