<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastica\ComponentTemplate;
use Elastica\Exception\InvalidException;
use Elastica\Test\Base as BaseTest;

/**
 * ComponentTemplate class tests.
 *
 * @internal
 */
class ComponentTemplateTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testInstantiate(): void
    {
        $name = 'component_template1';
        $client = $this->_getClient();
        $template = new ComponentTemplate($client, $name);

        $this->assertSame($client, $template->getClient());
        $this->assertEquals($name, $template->getName());
    }

    /**
     * @group unit
     */
    public function testIncorrectInstantiate(): void
    {
        $this->expectException(InvalidException::class);

        $client = $this->_getClient();
        new ComponentTemplate($client, null);
    }

    /**
     * @group functional
     */
    public function testCreateTemplate(): void
    {
        $componentTemplateArgs = [
            'template' => [
                'mappings' => [
                    'properties' => [
                        '@timestamp' => [
                            'type' => 'date',
                        ],
                    ],
                ],
            ],
        ];
        $name = 'component_template1';
        $template = new ComponentTemplate($this->_getClient(), $name);
        $template->create($componentTemplateArgs);
        $this->assertTrue($template->exists());
        $template->delete();
        $this->assertFalse($template->exists());
    }

    /**
     * @group functional
     */
    public function testCreateAlreadyExistsTemplateException(): void
    {
        $componentTemplateArgs = [
            'template' => [
                'mappings' => [
                    'properties' => [
                        '@timestamp' => [
                            'type' => 'date',
                        ],
                    ],
                ],
            ],
        ];
        $name = 'component_template1';
        $template = new ComponentTemplate($this->_getClient(), $name);
        $template->create($componentTemplateArgs);
        try {
            $template->create($componentTemplateArgs);
        } catch (ClientResponseException $e) {
            $error = \json_decode((string) $e->getResponse()->getBody(), true)['error']['root_cause'][0] ?? null;

            $this->assertNotEquals('index_template_already_exists_exception', $error['type']);
            $this->assertEquals('resource_already_exists_exception', $error['type']);
            $this->assertEquals(400, $e->getResponse()->getStatusCode());
        }
    }
}
