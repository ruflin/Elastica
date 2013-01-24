<?php

namespace Elastica\Test\Bulk;

use Elastica\Bulk\Action;
use Elastica\Document;
use Elastica\Test\Base as BaseTest;
use Elastica\Bulk\ResponseSet;
use Elastica\Response;

class ResponseSetTest extends BaseTest
{
    /**
     * @dataProvider invalidConstructorDataProvider
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testInvalidConstructor($responseData, $actions)
    {
        $response = new Response($responseData);
        $responseSet = new ResponseSet($response, $actions);
    }

    /**
     * @dataProvider isOkDataProvider
     */
    public function testIsOk($responseData, $actions, $expected)
    {
        $response = new Response($responseData);
        $responseSet = new ResponseSet($response, $actions);
        $this->assertEquals($expected, $responseSet->isOk());
    }

    public function testGetError()
    {
        list($responseData, $actions) = $this->_getFixture();
        $responseData['items'][1]['index']['ok'] = false;
        $responseData['items'][1]['index']['error'] = 'SomeExceptionMessage';
        $responseData['items'][2]['index']['ok'] = false;
        $responseData['items'][2]['index']['error'] = 'AnotherExceptionMessage';

        $response = new Response($responseData);
        $responseSet = new ResponseSet($response, $actions);

        $this->assertTrue($responseSet->hasError());
        $this->assertNotEquals('AnotherExceptionMessage', $responseSet->getError());
        $this->assertEquals('SomeExceptionMessage', $responseSet->getError());
    }

    public function testGetBulkResponses()
    {
        list($responseData, $actions) = $this->_getFixture();

        $response = new Response($responseData);
        $responseSet = new ResponseSet($response, $actions);

        $bulkResponses = $responseSet->getBulkResponses();
        $this->assertInternalType('array', $bulkResponses);
        $this->assertEquals(3, count($bulkResponses));

        foreach ($bulkResponses as $i => $bulkResponse) {
            $this->assertInstanceOf('Elastica\Bulk\Response', $bulkResponse);
            $bulkResponseData = $bulkResponse->getData();
            $this->assertInternalType('array', $bulkResponseData);
            $this->assertArrayHasKey('_id', $bulkResponseData);
            $this->assertEquals($responseData['items'][$i]['index']['_id'], $bulkResponseData['_id']);
            $this->assertSame($actions[$i], $bulkResponse->getAction());
            $this->assertEquals('index', $bulkResponse->getOpType());
        }
    }

    public function testIterator()
    {
        list($responseData, $actions) = $this->_getFixture();

        $response = new Response($responseData);
        $responseSet = new ResponseSet($response, $actions);

        $this->assertEquals(3, count($responseSet));

        foreach ($responseSet as $i => $bulkResponse) {
            $this->assertInstanceOf('Elastica\Bulk\Response', $bulkResponse);
            $bulkResponseData = $bulkResponse->getData();
            $this->assertInternalType('array', $bulkResponseData);
            $this->assertArrayHasKey('_id', $bulkResponseData);
            $this->assertEquals($responseData['items'][$i]['index']['_id'], $bulkResponseData['_id']);
            $this->assertSame($actions[$i], $bulkResponse->getAction());
            $this->assertEquals('index', $bulkResponse->getOpType());
        }

        $this->assertFalse($responseSet->valid());
        $this->assertNotInstanceOf('Elastica\Bulk\Response', $responseSet->current());
        $this->assertFalse($responseSet->current());

        $responseSet->next();

        $this->assertFalse($responseSet->valid());
        $this->assertNotInstanceOf('Elastica\Bulk\Response', $responseSet->current());
        $this->assertFalse($responseSet->current());

        $responseSet->rewind();

        $this->assertEquals(0, $responseSet->key());
        $this->assertTrue($responseSet->valid());
        $this->assertInstanceOf('Elastica\Bulk\Response', $responseSet->current());
    }

    public function invalidConstructorDataProvider()
    {
        list($responseData, $actions) = $this->_getFixture();

        $return = array();
        $return[] = array($responseData, array());
        $actions[2] = new \stdClass();
        $return[] = array($responseData, $actions);
        return $return;
    }

    public function isOkDataProvider()
    {

        list($responseData, $actions) = $this->_getFixture();

        $return = array();
        $return[] = array($responseData, $actions, true);
        $responseData['items'][2]['index']['ok'] = false;
        $return[] = array($responseData, $actions, false);
        return $return;
    }

    /**
     * @return array
     */
    protected function _getFixture()
    {
        $responseData = array(
            'took' => 5,
            'items' => array(
                array(
                    'index' => array(
                        '_index' => 'index',
                        '_type' => 'type',
                        '_id' => '1',
                        '_version' => 1,
                        'ok' => true,
                    )
                ),
                array(
                    'index' => array(
                        '_index' => 'index',
                        '_type' => 'type',
                        '_id' => '2',
                        '_version' => 1,
                        'ok' => true,
                    )
                ),
                array(
                    'index' => array(
                        '_index' => 'index',
                        '_type' => 'type',
                        '_id' => '3',
                        '_version' => 1,
                        'ok' => true,
                    )
                )
            )
        );
        $actions = array(
            new Action(Document::OP_TYPE_INDEX),
            new Action(Document::OP_TYPE_INDEX),
            new Action(Document::OP_TYPE_INDEX),
        );
        return array($responseData, $actions);
    }
}