<?php
namespace Elastica\Test\Transport;

use Elastica\Connection;
use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Search;
use Elastica\Transport\AbstractTransport;
use Elastica\Transport\Http;
use Elastica\Test\Base as BaseTest;

class AbstractTransportTest extends BaseTest
{
    /**
     * Return transport configuration and the expected HTTP method.
     *
     * @return array[]
     */
    public function getTransport()
    {
        return [
            [
                ['transport' => 'Http', 'curl' => [CURLINFO_HEADER_OUT => true]]
            ],
            [
                ['transport' => 'Guzzle', 'curl' => [CURLINFO_HEADER_OUT => true]]
            ]
        ];
    }

    /**
     * Return transport configuration and the expected HTTP method.
     *
     * @return array[]
     */
    public function getValidDefinitions()
    {
        $connection = new Connection();

        return [
            ['Http'],
            [['type' => 'Http']],
            [['type' => new Http()]],
            [new Http()],
            [DummyTransport::class],
        ];
    }

    /**
     * @group unit
     * @dataProvider getValidDefinitions
     */
    public function testCanCreateTransportInstances($transport)
    {
        $connection = new Connection();
        $params = [];
        $transport = AbstractTransport::create($transport, $connection, $params);
        $this->assertInstanceOf(AbstractTransport::class, $transport);
        $this->assertSame($connection, $transport->getConnection());
    }

    public function getInvalidDefinitions()
    {
        return [
            [['transport' => 'Http']],
            ['InvalidTransport'],
        ];
    }

    /**
     * @group unit
     * @dataProvider getInvalidDefinitions
     * @expectedException \Elastica\Exception\InvalidException
     * @expectedExceptionMessage Invalid transport
     */
    public function testThrowsExecptionOnInvalidTransportDefinition($transport)
    {
        AbstractTransport::create($transport, new Connection());
    }

    /**
     * @group unit
     */
    public function testCanInjectParamsWhenUsingArray()
    {
        $connection = new Connection();
        $params = [
            'param1' => 'some value',
            'param3' => 'value3',
        ];

        $transport = AbstractTransport::create([
            'type' => 'Http',
            'param1' => 'value1',
            'param2' => 'value2',
        ], $connection, $params);

        $this->assertSame('value1', $transport->getParam('param1'));
        $this->assertSame('value2', $transport->getParam('param2'));
        $this->assertSame('value3', $transport->getParam('param3'));
    }

    /**
     * This test check that boolean values in the querystring
     * are passed as string (true|false) and not with other values
     * due to boolean strict type in ES
     *
     * @group functional
     * @dataProvider getTransport
     */
    public function testBooleanStringValues($transport)
    {
        $client = $this->_getClient($transport);
        $index = $client->getIndex('elastica_testbooleanstringvalues');

        $doc = new Document(1, ['id' => 1, 'email' => 'test@test.com', 'username' => 'ruflin']);
        $index->getType('test')->addDocument($doc);
        $index->refresh();

        $search = new Search($index->getClient());
        $search->addIndex($index);

        // Added version param to result
        try {
            $results = $search->search([], ['version' => true]);
            $this->assertTrue(true);
        } catch (ResponseException $e) {
            $this->fail('Failed to parse value [1] as only [true] or [false] are allowed.');
        }


        if ($transport['transport'] == 'Http') {
            $info = $results->getResponse()->getTransferInfo();
            $url = $info['url'];
            $this->assertStringEndsWith('version=true', $url);
        }
    }
}
