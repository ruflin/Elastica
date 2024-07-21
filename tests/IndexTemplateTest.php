<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastica\Exception\InvalidException;
use Elastica\IndexTemplate;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * IndexTemplate class tests.
 *
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 *
 * @internal
 */
class IndexTemplateTest extends BaseTest
{
    #[Group('unit')]
    public function testInstantiate(): void
    {
        $name = 'index_template1';
        $client = $this->_getClient();
        $indexTemplate = new IndexTemplate($client, $name);

        $this->assertSame($client, $indexTemplate->getClient());
        $this->assertEquals($name, $indexTemplate->getName());
    }

    #[Group('unit')]
    public function testIncorrectInstantiate(): void
    {
        $this->expectException(InvalidException::class);

        $client = $this->_getClient();
        new IndexTemplate($client, null);
    }

    #[Group('functional')]
    public function testCreateTemplate(): void
    {
        $template = [
            'index_patterns' => 'te*',
            'settings' => [
                'number_of_shards' => 1,
            ],
        ];
        $name = 'index_template1';
        $indexTemplate = new IndexTemplate($this->_getClient(), $name);
        $indexTemplate->create($template);
        $this->assertTrue($indexTemplate->exists());
        $indexTemplate->delete();
        $this->assertFalse($indexTemplate->exists());
    }

    #[Group('functional')]
    public function testCreateAlreadyExistsTemplateException(): void
    {
        $template = [
            'index_patterns' => 'te*',
            'settings' => [
                'number_of_shards' => 1,
            ],
        ];
        $name = 'index_template1';
        $indexTemplate = new IndexTemplate($this->_getClient(), $name);
        $indexTemplate->create($template);
        try {
            $indexTemplate->create($template);
        } catch (ClientResponseException $e) {
            $error = \json_decode((string) $e->getResponse()->getBody(), true)['error']['root_cause'][0] ?? null;

            $this->assertNotEquals('index_template_already_exists_exception', $error['type']);
            $this->assertEquals('resource_already_exists_exception', $error['type']);
            $this->assertEquals(400, $e->getResponse()->getStatusCode());
        }
    }
}
