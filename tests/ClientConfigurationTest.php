<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastica\ClientConfiguration;
use Elastica\Exception\InvalidException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Group('unit')]
class ClientConfigurationTest extends TestCase
{
    public function testFromEmptyArray(): void
    {
        $configuration = ClientConfiguration::fromArray([]);

        $expected = [
            'hosts' => [ClientConfiguration::DEFAULT_HOST],
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
            'hosts' => [ClientConfiguration::DEFAULT_HOST],
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
        $this->assertTrue($configuration->has('hosts'));
        $this->assertFalse($configuration->has('inexistantKey'));
    }

    public function testGet(): void
    {
        $configuration = new ClientConfiguration();

        $expected = [
            'hosts' => [ClientConfiguration::DEFAULT_HOST],
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
