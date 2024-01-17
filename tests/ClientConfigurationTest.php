<?php

namespace Elastica\Test;

use Elastica\ClientConfiguration;
use Elastica\Exception\InvalidException;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 *
 * @internal
 */
class ClientConfigurationTest extends TestCase
{
    public function testInvalidDsn(): void
    {
        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('DSN "test foo" is invalid.');

        ClientConfiguration::fromDsn('test foo');
    }

    public function testInvalidDsnPortOnly(): void
    {
        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('DSN ":0" is invalid.');

        ClientConfiguration::fromDsn(':0');
    }

    public function testFromSimpleDsn(): void
    {
        $configuration = ClientConfiguration::fromDsn('192.168.1.1:9201');

        $expected = [
            'host' => '192.168.1.1',
            'port' => 9201,
            'path' => null,
            'url' => null,
            'connections' => [],
            'roundRobin' => false,
            'retryOnConflict' => 0,
            'username' => null,
            'password' => null,
            'transport_config' => [],
        ];

        $this->assertEquals($expected, $configuration->getAll());
    }

    public function testFromDsnWithParameters(): void
    {
        $configuration = ClientConfiguration::fromDsn('https://user:p4ss@foo.com:9201/my-path?roundRobin=true&retryOnConflict=2&extra=abc');
        $expected = [
            'host' => 'foo.com',
            'port' => 9201,
            'path' => '/my-path',
            'url' => null,
            'connections' => [],
            'roundRobin' => true,
            'retryOnConflict' => 2,
            'username' => 'user',
            'password' => 'p4ss',
            'extra' => 'abc',
            'transport_config' => [],
        ];

        $this->assertEquals($expected, $configuration->getAll());
    }

    public function testFromDsnWithPool(): void
    {
        $configuration = ClientConfiguration::fromDsn('pool(http://nicolas@127.0.0.1 http://127.0.0.2/bar?timeout=4)?extra=abc&username=tobias');
        $expected = [
            'host' => null,
            'port' => null,
            'path' => null,
            'url' => null,
            'connections' => [
                ['host' => '127.0.0.1', 'username' => 'nicolas'],
                ['host' => '127.0.0.2', 'path' => '/bar', 'timeout' => 4],
            ],
            'roundRobin' => false,
            'retryOnConflict' => 0,
            'username' => 'tobias',
            'password' => null,
            'extra' => 'abc',
            'transport_config' => [],
        ];

        $this->assertEquals($expected, $configuration->getAll());
    }

    public function testFromEmptyArray(): void
    {
        $configuration = ClientConfiguration::fromArray([]);

        $expected = [
            'host' => null,
            'port' => null,
            'path' => null,
            'url' => null,
            'connections' => [], // host, port, path, timeout, transport, compression, persistent, timeout, username, password, config -> (curl, headers, url)
            'roundRobin' => false,
            'retryOnConflict' => 0,
            'username' => null,
            'password' => null,
            'transport_config' => [],
        ];

        $this->assertEquals($expected, $configuration->getAll());
    }

    public function testFromArray(): void
    {
        $configuration = ClientConfiguration::fromArray([
            'username' => 'Jdoe',
            'extra' => 'abc',
        ]);

        $expected = [
            'host' => null,
            'port' => null,
            'path' => null,
            'url' => null,
            'connections' => [], // host, port, path, timeout, transport, compression, persistent, timeout, username, password, config -> (curl, headers, url)
            'roundRobin' => false,
            'retryOnConflict' => 0,
            'username' => 'Jdoe',
            'password' => null,
            'transport_config' => [],
            'extra' => 'abc',
        ];

        $this->assertEquals($expected, $configuration->getAll());
    }

    public function testHas(): void
    {
        $configuration = new ClientConfiguration();
        $this->assertTrue($configuration->has('host'));
        $this->assertFalse($configuration->has('inexistantKey'));
    }

    public function testGet(): void
    {
        $configuration = new ClientConfiguration();

        $expected = [
            'host' => null,
            'port' => null,
            'path' => null,
            'url' => null,
            'connections' => [],
            'roundRobin' => false,
            'retryOnConflict' => 0,
            'username' => null,
            'password' => null,
            'transport_config' => [],
        ];

        $this->assertEquals($expected, $configuration->get(''));

        $this->expectException(InvalidException::class);
        $configuration->get('invalidKey');
    }

    public function testAdd(): void
    {
        $keyName = 'myKey';

        $configuration = new ClientConfiguration();
        $this->assertFalse($configuration->has($keyName));

        $configuration->add($keyName, 'FirstValue');
        $this->assertEquals(['FirstValue'], $configuration->get($keyName));

        $configuration->add($keyName, 'SecondValue');
        $this->assertEquals(['FirstValue', 'SecondValue'], $configuration->get($keyName));

        $configuration->set('otherKey', 'value');
        $this->assertEquals('value', $configuration->get('otherKey'));
        $configuration->add('otherKey', 'nextValue');
        $this->assertEquals(['value', 'nextValue'], $configuration->get('otherKey'));
    }
}
