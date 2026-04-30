<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastica\Client;
use Elastica\ClientConfiguration;
use Elastica\Exception\ExceptionInterface;
use Elastica\Exception\InvalidException;
use Elastica\Exception\NotImplementedException;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('unit')]
class ClientTest extends BaseTest
{
    public function testItConstruct(): void
    {
        $client = new Client();

        $expected = [
            'hosts' => [ClientConfiguration::DEFAULT_HOST],
            'retryOnConflict' => 0,
            'username' => null,
            'password' => null,
            'transport_config' => [],
        ];

        $this->assertEquals($expected, $client->getConfig());
    }

    public function testItConstructWithStringAsConfig(): void
    {
        $client = new Client('https://user:p4ss@foo.com:9200?retryOnConflict=2');

        $expected = [
            'hosts' => ['https://user:p4ss@foo.com:9200?retryOnConflict=2'],
            'retryOnConflict' => 0,
            'username' => null,
            'password' => null,
            'transport_config' => [],
        ];

        $this->assertEquals($expected, $client->getConfig());
    }

    public function testItAddsHosts(): void
    {
        $hosts = ['https://my-host.com:9300'];

        $client = new Client(['hosts' => $hosts]);

        $this->assertEquals($hosts, $client->getConfig('hosts'));
        $this->assertEquals('https://my-host.com:9300', $client->getTransport()->getNodePool()->nextNode()->getUri());
    }

    public function testAddDocumentsEmpty(): void
    {
        $this->expectException(InvalidException::class);

        $client = $this->_getClient();
        $client->addDocuments([]);
    }

    public function testConfigValue(): void
    {
        $config = [
            'level1' => [
                'level2' => [
                    'level3' => 'value3',
                ],
                'level21' => 'value21',
            ],
            'level11' => 'value11',
        ];
        $client = new Client($config);

        $this->assertNull($client->getConfigValue('level12'));
        $this->assertFalse($client->getConfigValue('level12', false));
        $this->assertEquals(10, $client->getConfigValue('level12', 10));

        $this->assertEquals('value11', $client->getConfigValue('level11'));
        $this->assertNotNull($client->getConfigValue('level11'));
        $this->assertNotEquals(false, $client->getConfigValue('level11', false));
        $this->assertNotEquals(10, $client->getConfigValue('level11', 10));

        $this->assertEquals('value3', $client->getConfigValue(['level1', 'level2', 'level3']));
        $this->assertIsArray($client->getConfigValue(['level1', 'level2']));
    }

    public function testPassBigIntSettingsToConnectionConfig(): void
    {
        $client = new Client(['bigintConversion' => true]);

        $this->assertTrue($client->getConfig('bigintConversion'));
    }

    public function testGetAsync(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Not supported');

        $client = new Client();
        $client->getAsync();
    }

    public function testSetElasticMetaHeader(): void
    {
        $client = new Client();
        $client->setElasticMetaHeader(true);

        $this->assertTrue($client->getElasticMetaHeader());
    }

    public function testSetAsync(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Not supported');

        $client = new Client();
        $client->setAsync(true);
    }

    public function testSetResponseException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Not supported');

        $client = new Client();
        $client->setResponseException(true);
    }

    public function testGetResponseException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Not supported');

        $client = new Client();
        $client->getResponseException();
    }

    public function testClientConnectionWithCloudId(): void
    {
        $client = new Client([
            'hosts' => ['foo.com:9200'],
            'cloud_id' => 'Test:ZXUtY2VudHJhbC0xLmF3cy5jbG91ZC5lcy5pbyQ0ZGU0NmNlZDhkOGQ0NTk2OTZlNTQ0ZmU1ZjMyYjk5OSRlY2I0YTJlZmY0OTA0ZDliOTE5NzMzMmQwOWNjOTY5Ng==',
            'retryOnConflict' => 2,
            'username' => 'user',
            'password' => 'p4ss',
            'transport_config' => [],
        ]);

        $node = $client->getTransport()->getNodePool()->nextNode();

        $this->assertEquals('4de46ced8d8d459696e544fe5f32b999.eu-central-1.aws.cloud.es.io', $node->getUri()->getHost());
    }

    public function testItThrowsAnExceptionWhenApiKeyAndUserNameInConfigAtTheSameTime(): void
    {
        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('You cannot use APIKey and Basic Authentication together.');

        new Client([
            'username' => 'user',
            'api_key' => 'key',
        ]);
    }

    public function testItSetsAuthorizationHeaderIfApiKeyPassed(): void
    {
        $apiKey = 'key';

        $client = new Client(['api_key' => $apiKey]);

        self::assertSame(['Authorization' => \sprintf('ApiKey %s', $apiKey)], $client->getTransport()->getHeaders());
    }

    /**
     * @covers \Elastica\Client::_buildTransport
     */
    public function testRetriesConfigurationWithZeroRetries(): void
    {
        $client = new Client(['retries' => 0]);

        // Verify that zero retries is properly set on the transport
        $transport = $client->getTransport();
        $this->assertEquals(0, $transport->getRetries());
    }

    /**
     * @covers \Elastica\Client::_buildTransport
     */
    public function testRetriesConfigurationWithPositiveRetries(): void
    {
        $client = new Client(['retries' => 3]);

        // Verify that positive retries is properly set on the transport
        $transport = $client->getTransport();
        $this->assertEquals(3, $transport->getRetries());
    }

    /**
     * @covers \Elastica\Client::_buildTransport
     */
    public function testRetriesConfigurationWithNegativeRetries(): void
    {
        $client = new Client(['retries' => -1]);

        // Verify that negative retries falls back to default (number of hosts)
        $transport = $client->getTransport();
        $this->assertEquals(1, $transport->getRetries()); // Default host count
    }

    /**
     * @covers \Elastica\Client::_buildTransport
     */
    public function testRetriesConfigurationWithMultipleHosts(): void
    {
        $client = new Client([
            'hosts' => ['localhost:9200', 'localhost:9201', 'localhost:9202'],
            'retries' => 0,
        ]);

        // Verify that zero retries is properly set even with multiple hosts
        $transport = $client->getTransport();
        $this->assertEquals(0, $transport->getRetries());
    }

    /**
     * @covers \Elastica\Client::_buildTransport
     */
    public function testRetriesConfigurationDefaultBehavior(): void
    {
        $client = new Client(['hosts' => ['localhost:9200', 'localhost:9201']]);

        // Verify that when no retries is specified, it defaults to host count
        $transport = $client->getTransport();
        $this->assertEquals(2, $transport->getRetries()); // Number of hosts
    }

    /**
     * @covers \Elastica\Client::_buildTransport
     */
    public function testRetriesConfigurationWithStringZero(): void
    {
        $client = new Client(['retries' => '0']);

        // Verify that string '0' is properly converted to integer 0
        $transport = $client->getTransport();
        $this->assertEquals(0, $transport->getRetries());
    }

    /**
     * @return iterable<string, array{0: callable(Client): mixed}>
     */
    public static function unsupportedClientFeaturesProvider(): iterable
    {
        yield 'setAsync' => [static fn (Client $client) => $client->setAsync(true)];
        yield 'getAsync' => [static fn (Client $client) => $client->getAsync()];
        yield 'setResponseException' => [static fn (Client $client) => $client->setResponseException(true)];
        yield 'getResponseException' => [static fn (Client $client) => $client->getResponseException()];
        yield 'setServerless' => [static fn (Client $client) => $client->setServerless(true)];
    }

    #[DataProvider('unsupportedClientFeaturesProvider')]
    public function testUnsupportedFeaturesThrowNotImplementedException(callable $invocation): void
    {
        $client = new Client();

        $this->expectException(NotImplementedException::class);

        $invocation($client);
    }

    public function testNotImplementedExceptionIsCatchableAsElasticaException(): void
    {
        $client = new Client();

        try {
            $client->setAsync(true);
            $this->fail('Expected NotImplementedException was not thrown.');
        } catch (ExceptionInterface $e) {
            $this->assertInstanceOf(NotImplementedException::class, $e);
            $this->assertInstanceOf(\BadMethodCallException::class, $e);
        }
    }
}
