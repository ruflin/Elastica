<?php

declare(strict_types=1);

namespace Elastica\Test\Bulk;

use Elastica\Bulk;
use Elastica\Bulk\Action;
use Elastica\Bulk\ResponseSet;
use Elastica\Client;
use Elastica\Exception\Bulk\Response\ActionException;
use Elastica\Exception\Bulk\ResponseException;
use Elastica\Response;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('unit')]
class ResponseSetTest extends BaseTest
{
    public function testConstructor(): void
    {
        [$responseData, $actions] = self::_getFixture();

        $responseSet = $this->_createResponseSet($responseData, $actions);

        $this->assertEquals(200, $responseSet->getStatus());
    }

    #[DataProvider('isOkDataProvider')]
    public function testIsOk(array $responseData, array $actions, bool $expected): void
    {
        $responseSet = $this->_createResponseSet($responseData, $actions);
        $this->assertEquals($expected, $responseSet->isOk());
    }

    public static function isOkDataProvider(): \Generator
    {
        [$responseData, $actions] = self::_getFixture();

        yield [$responseData, $actions, true];
        $responseData['items'][2]['index']['ok'] = false;
        yield [$responseData, $actions, false];
    }

    public function testGetError(): void
    {
        [$responseData, $actions] = self::_getFixture();
        $responseData['items'][1]['index']['ok'] = false;
        $responseData['items'][1]['index']['error'] = 'SomeExceptionMessage';
        $responseData['items'][2]['index']['ok'] = false;
        $responseData['items'][2]['index']['error'] = 'AnotherExceptionMessage';

        try {
            $this->_createResponseSet($responseData, $actions);
            $this->fail('Bulk request should fail');
        } catch (ResponseException $e) {
            $responseSet = $e->getResponseSet();

            $this->assertTrue($responseSet->hasError());
            $this->assertEquals('SomeExceptionMessage', $responseSet->getError());
            $this->assertNotEquals('AnotherExceptionMessage', $responseSet->getError());

            $actionExceptions = $e->getActionExceptions();
            $this->assertCount(2, $actionExceptions);

            $this->assertInstanceOf(ActionException::class, $actionExceptions[0]);
            $this->assertSame($actions[1], $actionExceptions[0]->getAction());
            $this->assertStringContainsString('SomeExceptionMessage', $actionExceptions[0]->getMessage());
            $this->assertTrue($actionExceptions[0]->getResponse()->hasError());

            $this->assertInstanceOf(ActionException::class, $actionExceptions[1]);
            $this->assertSame($actions[2], $actionExceptions[1]->getAction());
            $this->assertStringContainsString('AnotherExceptionMessage', $actionExceptions[1]->getMessage());
            $this->assertTrue($actionExceptions[1]->getResponse()->hasError());
        }
    }

    public function testGetBulkResponses(): void
    {
        [$responseData, $actions] = self::_getFixture();

        $responseSet = $this->_createResponseSet($responseData, $actions);

        $bulkResponses = $responseSet->getBulkResponses();
        $this->assertIsArray($bulkResponses);
        $this->assertCount(3, $bulkResponses);

        foreach ($bulkResponses as $i => $bulkResponse) {
            $this->assertInstanceOf(Bulk\Response::class, $bulkResponse);
            $bulkResponseData = $bulkResponse->getData();
            $this->assertIsArray($bulkResponseData);
            $this->assertArrayHasKey('_id', $bulkResponseData);
            $this->assertEquals($responseData['items'][$i]['index']['_id'], $bulkResponseData['_id']);
            $this->assertSame($actions[$i], $bulkResponse->getAction());
            $this->assertEquals('index', $bulkResponse->getOpType());
        }
    }

    public function testIterator(): void
    {
        [$responseData, $actions] = self::_getFixture();

        $responseSet = $this->_createResponseSet($responseData, $actions);

        $this->assertCount(3, $responseSet);

        foreach ($responseSet as $i => $bulkResponse) {
            $this->assertInstanceOf(Bulk\Response::class, $bulkResponse);
            $bulkResponseData = $bulkResponse->getData();
            $this->assertIsArray($bulkResponseData);
            $this->assertArrayHasKey('_id', $bulkResponseData);
            $this->assertEquals($responseData['items'][$i]['index']['_id'], $bulkResponseData['_id']);
            $this->assertSame($actions[$i], $bulkResponse->getAction());
            $this->assertEquals('index', $bulkResponse->getOpType());
        }

        $this->assertFalse($responseSet->valid());

        $responseSet->rewind();

        $this->assertEquals(0, $responseSet->key());
        $this->assertTrue($responseSet->valid());
    }

    protected function _createResponseSet(array $responseData, array $actions): ResponseSet
    {
        $client = $this->createMock(Client::class);

        $response = new Response($responseData, 200);

        $client->expects($this->once())
            ->method('baseBulk')
            ->withAnyParameters()
            ->willReturn($response)
        ;

        $bulk = new Bulk($client);
        $bulk->addActions($actions);

        return $bulk->send();
    }

    protected static function _getFixture(): array
    {
        $responseData = [
            'took' => 5,
            'items' => [
                [
                    'index' => [
                        '_index' => 'index',
                        '_id' => '1',
                        '_version' => 1,
                        'ok' => true,
                    ],
                ],
                [
                    'index' => [
                        '_index' => 'index',
                        '_id' => '2',
                        '_version' => 1,
                        'ok' => true,
                    ],
                ],
                [
                    'index' => [
                        '_index' => 'index',
                        '_id' => '3',
                        '_version' => 1,
                        'ok' => true,
                    ],
                ],
            ],
        ];
        $bulkResponses = [
            new Action(),
            new Action(),
            new Action(),
        ];

        return [$responseData, $bulkResponses];
    }
}
