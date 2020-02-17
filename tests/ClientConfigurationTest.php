<?php

namespace Elastica\Test;

use Elastica\ClientConfiguration;
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
        $this->expectException(\Elastica\Exception\InvalidException::class);
        $this->expectExceptionMessage("DSN 'test:0' is invalid.");

        ClientConfiguration::fromDsn('test:0');
    }

    public function testFromSimpleDsn(): void
    {
        $configuration = ClientConfiguration::fromDsn('192.168.1.1:9201');

        $expected = [
            'host' => '192.168.1.1',
            'port' => 9201,
            'path' => null,
            'url' => null,
            'proxy' => null,
            'transport' => null,
            'persistent' => true,
            'timeout' => null,
            'connections' => [],
            'roundRobin' => false,
            'retryOnConflict' => 0,
            'bigintConversion' => false,
            'username' => null,
            'password' => null,
        ];

        $this->assertEquals($expected, $configuration->getAll());
    }

    public function testFromDsnWithParameters(): void
    {
        $configuration = ClientConfiguration::fromDsn('https://user:p4ss@foo.com:9201/my-path?proxy=https://proxy.com&persistent=false&timeout=45&roundRobin=true&retryOnConflict=2&bigintConversion=true&extra=abc');
        $expected = [
            'host' => 'foo.com',
            'port' => '9201',
            'path' => '/my-path',
            'url' => null,
            'proxy' => 'https://proxy.com',
            'transport' => 'https',
            'persistent' => false,
            'timeout' => 45,
            'connections' => [],
            'roundRobin' => true,
            'retryOnConflict' => 2,
            'bigintConversion' => true,
            'username' => 'user',
            'password' => 'p4ss',
            'extra' => 'abc',
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
            'proxy' => null,
            'transport' => null,
            'persistent' => true,
            'timeout' => null,
            'connections' => [], // host, port, path, timeout, transport, compression, persistent, timeout, username, password, config -> (curl, headers, url)
            'roundRobin' => false,
            'retryOnConflict' => 0,
            'bigintConversion' => false,
            'username' => null,
            'password' => null,
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
            'proxy' => null,
            'transport' => null,
            'persistent' => true,
            'timeout' => null,
            'connections' => [], // host, port, path, timeout, transport, compression, persistent, timeout, username, password, config -> (curl, headers, url)
            'roundRobin' => false,
            'retryOnConflict' => 0,
            'bigintConversion' => false,
            'username' => 'Jdoe',
            'password' => null,
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
        $this->assertTrue($configuration->get('persistent'));

        $expected = [
            'host' => null,
            'port' => null,
            'path' => null,
            'url' => null,
            'proxy' => null,
            'transport' => null,
            'persistent' => true,
            'timeout' => null,
            'connections' => [],
            'roundRobin' => false,
            'retryOnConflict' => 0,
            'bigintConversion' => false,
            'username' => null,
            'password' => null,
        ];

        $this->assertEquals($expected, $configuration->get(''));

        $this->expectException(\Elastica\Exception\InvalidException::class);
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
