<?php

namespace Elastica\Test\Connection\Strategy;

use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastic\Elasticsearch\Transport\Adapter\AdapterOptions;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Elastic\Transport\TransportBuilder;
use Elastica\Client;
use Elastica\Connection;
use Elastica\Connection\Strategy\RoundRobin;
use Elastica\ResponseConverter;
use Elastica\Test\Base;
use GuzzleHttp\RequestOptions;
use Psr\Http\Client\ClientInterface as HttpClientInterface;

/**
 * Description of RoundRobinTest.
 *
 * @author chabior
 *
 * @internal
 */
class RoundRobinTest extends Base
{
    /**
     * @var int Number of seconds to wait before timeout is called. Is set low for tests to have fast tests.
     */
    protected $_timeout = 1;

    /**
     * @group functional
     */
    public function testConnection(): void
    {
        $config = ['connectionStrategy' => 'RoundRobin'];
        $client = $this->_getClient($config);
        $response = $client->indices()->getAlias();

        $this->_checkResponse($response);

        $this->_checkStrategy($client);
    }

    /**
     * @group unit
     */
    public function testOldStrategySet(): void
    {
        $config = ['roundRobin' => true];
        $client = $this->_getClient($config);

        $this->_checkStrategy($client);
    }

    /**
     * @group functional
     */
    public function testFailConnection(): void
    {
        $this->expectException(NoNodeAvailableException::class);

        $config = [
            'connectionStrategy' => 'RoundRobin',
            'host' => '255.255.255.0',
            'timeout' => $this->_timeout,
            'transport_config' => [
                'http_client_options' => [
                    RequestOptions::TIMEOUT => 1,
                    RequestOptions::CONNECT_TIMEOUT => 1,
                ],
            ],
        ];
        $client = $this->_getClient($config);

        $this->_checkStrategy($client);

        $client->indices()->getAlias();
    }

    /**
     * @group functional
     */
    public function testWithOneFailConnection(): void
    {
        $httpClientOptions = [
            RequestOptions::TIMEOUT => 1,
            RequestOptions::CONNECT_TIMEOUT => 1,
        ];

        $transportConnectionBuilder1 = TransportBuilder::create();
        $transportConnectionBuilder1->setHosts(['255.255.255.0']);
        $transportConnectionBuilder1->setClient(
            $this->setHttpClientOptions($transportConnectionBuilder1->getClient(), [], $httpClientOptions)
        );

        $transportConnectionBuilder2 = TransportBuilder::create();
        $transportConnectionBuilder2->setHosts([$this->_getHost().':'.$this->_getPort()]);
        $transportConnectionBuilder2->setClient(
            $this->setHttpClientOptions($transportConnectionBuilder1->getClient(), [], $httpClientOptions)
        );

        $connections = [
            new Connection(['host' => '255.255.255.0', 'timeout' => $this->_timeout, 'transport' => $transportConnectionBuilder1->build()]),
            new Connection(['host' => $this->_getHost(), 'timeout' => $this->_timeout, 'transport' => $transportConnectionBuilder2->build()]),
        ];

        $count = 0;
        $callback = static function ($connection, $exception, $client) use (&$count): void {
            ++$count;
        };

        $client = $this->_getClient(['connectionStrategy' => 'RoundRobin'], $callback);
        $client->setConnections($connections);

        $response = $client->indices()->getAlias();

        $this->_checkResponse($response);

        $this->_checkStrategy($client);

        $this->assertLessThan(\count($connections), $count);
    }

    /**
     * @group functional
     */
    public function testWithNoValidConnection(): void
    {
        $httpClientOptions = [
            RequestOptions::TIMEOUT => 1,
            RequestOptions::CONNECT_TIMEOUT => 1,
        ];

        $transportConnectionBuilder1 = TransportBuilder::create();
        $transportConnectionBuilder1->setHosts(['255.255.255.0']);
        $transportConnectionBuilder1->setClient(
            $this->setHttpClientOptions($transportConnectionBuilder1->getClient(), [], $httpClientOptions)
        );

        $transportConnectionBuilder2 = TransportBuilder::create();
        $transportConnectionBuilder2->setHosts(['45.45.45.45:80']);
        $transportConnectionBuilder2->setClient(
            $this->setHttpClientOptions($transportConnectionBuilder1->getClient(), [], $httpClientOptions)
        );

        $transportConnectionBuilder3 = TransportBuilder::create();
        $transportConnectionBuilder3->setHosts(['10.123.213.123']);
        $transportConnectionBuilder3->setClient(
            $this->setHttpClientOptions($transportConnectionBuilder1->getClient(), [], $httpClientOptions)
        );

        $connections = [
            new Connection([
                'host' => '255.255.255.0',
                'timeout' => $this->_timeout,
                'transport' => $transportConnectionBuilder1->build(),
            ]),
            new Connection([
                'host' => '45.45.45.45',
                'port' => '80',
                'timeout' => $this->_timeout,
                'transport' => $transportConnectionBuilder2->build(),
            ]),
            new Connection([
                'host' => '10.123.213.123',
                'timeout' => $this->_timeout,
                'transport' => $transportConnectionBuilder3->build(),
            ]),
        ];

        $count = 0;
        $client = $this->_getClient(['roundRobin' => true], static function () use (&$count): void {
            ++$count;
        });

        $client->setConnections($connections);

        try {
            $client->indices()->getAlias();
            $this->fail('Should throw exception as no connection valid');
        } catch (NoNodeAvailableException $e) {
            $this->assertEquals(\count($connections), $count);
            $this->_checkStrategy($client);
        }
    }

    protected function _checkStrategy(Client $client): void
    {
        $strategy = $client->getConnectionStrategy();

        $this->assertInstanceOf(RoundRobin::class, $strategy);
    }

    protected function _checkResponse(Elasticsearch $response): void
    {
        $responseElastica = ResponseConverter::toElastica($response);

        $this->assertTrue($responseElastica->isOk());
    }

    protected function setHttpClientOptions(HttpClientInterface $client, array $config, array $clientOptions = []): HttpClientInterface
    {
        if (empty($config) && empty($clientOptions)) {
            return $client;
        }
        $class = \get_class($client);
        $adapterClass = AdapterOptions::HTTP_ADAPTERS[$class];

        $adapter = new $adapterClass();

        return $adapter->setConfig($client, $config, $clientOptions);
    }
}
