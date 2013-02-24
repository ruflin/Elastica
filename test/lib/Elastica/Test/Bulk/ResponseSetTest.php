<?php

namespace Elastica\Test\Bulk;

use Elastica\Bulk\Action;
use Elastica\Bulk;
use Elastica\Exception\Bulk\ResponseException;
use Elastica\Test\Base as BaseTest;
use Elastica\Bulk\ResponseSet;
use Elastica\Response;

class ResponseSetTest extends BaseTest
{
    /**
     * @dataProvider isOkDataProvider
     */
    public function testIsOk($responseData, $actions, $expected)
    {
        $responseSet = $this->_createResponseSet($responseData, $actions);
        $this->assertEquals($expected, $responseSet->isOk());
    }

    public function testGetError()
    {
        list($responseData, $actions) = $this->_getFixture();
        $responseData['items'][1]['index']['ok'] = false;
        $responseData['items'][1]['index']['error'] = 'SomeExceptionMessage';
        $responseData['items'][2]['index']['ok'] = false;
        $responseData['items'][2]['index']['error'] = 'AnotherExceptionMessage';

        try {
            $this->_createResponseSet($responseData, $actions);
            $this->fail('Bulk request should fail');
        } catch (ResponseException $e) {
            $responseSet = $e->getResponseSet();

            $this->assertInstanceOf('Elastica\\Bulk\\ResponseSet', $responseSet);

            $this->assertTrue($responseSet->hasError());
            $this->assertNotEquals('AnotherExceptionMessage', $responseSet->getError());
            $this->assertEquals('SomeExceptionMessage', $responseSet->getError());

            $actionExceptions = $e->getActionExceptions();
            $this->assertEquals(2, count($actionExceptions));

            $this->assertInstanceOf('Elastica\Exception\Bulk\Response\ActionException', $actionExceptions[0]);
            $this->assertSame($actions[1], $actionExceptions[0]->getAction());
            $this->assertContains('SomeExceptionMessage', $actionExceptions[0]->getMessage());
            $this->assertTrue($actionExceptions[0]->getResponse()->hasError());

            $this->assertInstanceOf('Elastica\Exception\Bulk\Response\ActionException', $actionExceptions[1]);
            $this->assertSame($actions[2], $actionExceptions[1]->getAction());
            $this->assertContains('AnotherExceptionMessage', $actionExceptions[1]->getMessage());
            $this->assertTrue($actionExceptions[1]->getResponse()->hasError());
        }
    }

    public function testGetBulkResponses()
    {
        list($responseData, $actions) = $this->_getFixture();

        $responseSet = $this->_createResponseSet($responseData, $actions);

        $bulkResponses = $responseSet->getBulkResponses();
        $this->assertInternalType('array', $bulkResponses);
        $this->assertEquals(3, count($bulkResponses));

        foreach ($bulkResponses as $i => $bulkResponse) {
            $this->assertInstanceOf('Elastica\\Bulk\\Response', $bulkResponse);
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

        $responseSet = $this->_createResponseSet($responseData, $actions);

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
     * @param array $responseData
     * @param array $actions
     * @return \Elastica\Bulk\ResponseSet
     */
    protected function _createResponseSet(array $responseData, array $actions)
    {
        $client = $this->getMock('Elastica\\Client', array('request'));

        $client->expects($this->once())
            ->method('request')
            ->withAnyParameters()
            ->will($this->returnValue(new Response($responseData)));

        $bulk = new Bulk($client);
        $bulk->addActions($actions);
        return $bulk->send();
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
        $bulkResponses = array(
            new Action(),
            new Action(),
            new Action(),
        );
        return array($responseData, $bulkResponses);
    }
}