<?php

namespace Elastica\Test\Exception;

use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Mapping;

/**
 * @internal
 */
class ResponseExceptionTest extends AbstractExceptionTest
{
    /**
     * @group functional
     */
    public function testCreateExistingIndex(): void
    {
        $this->_createIndex('woo', true);

        try {
            $this->_createIndex('woo', false);
            $this->fail('Index created when it should fail');
        } catch (ResponseException $ex) {
            $error = $ex->getResponse()->getFullError();

            $this->assertNotEquals('index_already_exists_exception', $error['type']);
            $this->assertEquals('resource_already_exists_exception', $error['type']);
            $this->assertEquals(400, $ex->getResponse()->getStatus());
        }
    }

    /**
     * @group functional
     */
    public function testBadType(): void
    {
        $index = $this->_createIndex();

        $index->setMapping(new Mapping([
            'num' => [
                'type' => 'long',
            ],
        ]));

        try {
            $index->addDocument(new Document('', [
                'num' => 'not number at all',
            ]));
            $this->fail('Indexing with wrong type should fail');
        } catch (ResponseException $ex) {
            $error = $ex->getResponse()->getFullError();
            $this->assertEquals('mapper_parsing_exception', $error['type']);
            $this->assertEquals(400, $ex->getResponse()->getStatus());
        }
    }

    /**
     * @group functional
     */
    public function testWhatever(): void
    {
        $index = $this->_createIndex();
        $index->delete();

        try {
            $index->search();
        } catch (ResponseException $ex) {
            $error = $ex->getResponse()->getFullError();
            $this->assertEquals('index_not_found_exception', $error['type']);
            $this->assertEquals(404, $ex->getResponse()->getStatus());
        }
    }
}
