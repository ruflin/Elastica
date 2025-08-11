<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastica\Exception\InvalidException;
use Elastica\Template;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * Template class tests.
 *
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 *
 * @internal
 */
class TemplateTest extends BaseTest
{
    #[Group('unit')]
    public function testInstantiate(): void
    {
        $name = 'template1';
        $client = $this->_getClient();
        $template = new Template($client, $name);

        $this->assertSame($client, $template->getClient());
        $this->assertEquals($name, $template->getName());
    }

    #[Group('unit')]
    public function testIncorrectInstantiate(): void
    {
        $this->expectException(InvalidException::class);

        $client = $this->_getClient();
        new Template($client, null);
    }

    #[Group('functional')]
    public function testCreateTemplate(): void
    {
        $templateArgs = [
            'index_patterns' => 'oldte*',
            'settings' => [
                'number_of_shards' => 1,
            ],
        ];
        $name = 'template1';
        $template = new Template($this->_getClient(), $name);
        $template->create($templateArgs);
        $this->assertTrue($template->exists());
        $template->delete();
        $this->assertFalse($template->exists());
    }

    #[Group('functional')]
    public function testCreateAlreadyExistsTemplateException(): void
    {
        $templateArgs = [
            'index_patterns' => 'oldte*',
            'settings' => [
                'number_of_shards' => 1,
            ],
        ];
        $name = 'template1';
        $template = new Template($this->_getClient(), $name);
        $template->create($templateArgs);
        try {
            $template->create($templateArgs);
        } catch (ClientResponseException $e) {
            $error = \json_decode((string) $e->getResponse()->getBody(), true)['error']['root_cause'][0] ?? null;

            $this->assertNotEquals('index_template_already_exists_exception', $error['type']);
            $this->assertEquals('resource_already_exists_exception', $error['type']);
            $this->assertEquals(400, $e->getResponse()->getStatusCode());
        }
    }
}
