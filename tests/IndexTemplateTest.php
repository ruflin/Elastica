<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Exception\ResponseException;
use Elastica\IndexTemplate;
use Elastica\Request;
use Elastica\Response;
use Elastica\Test\Base as BaseTest;

/**
 * IndexTemplate class tests.
 *
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 */
class IndexTemplateTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testInstantiate()
    {
        $name = 'index_template1';
        $client = $this->_getClient();
        $indexTemplate = new IndexTemplate($client, $name);
        $indexTemplate->getName();
        $this->assertSame($client, $indexTemplate->getClient());
        $this->assertEquals($name, $indexTemplate->getName());
    }

    /**
     * @group unit
     */
    public function testIncorrectInstantiate()
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);

        $client = $this->_getClient();
        new IndexTemplate($client, null);
    }

    /**
     * @group unit
     */
    public function testDelete()
    {
        $name = 'index_template1';
        $response = new Response('');
        /** @var \PHPUnit_Framework_MockObject_MockObject|Client $clientMock */
        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())
            ->method('request')
            ->with('_template/'.$name, Request::DELETE, [], [])
            ->willReturn($response);
        $indexTemplate = new IndexTemplate($clientMock, $name);
        $this->assertSame($response, $indexTemplate->delete());
    }

    /**
     * @group unit
     */
    public function testCreate()
    {
        $args = [1];
        $response = new Response('');
        $name = 'index_template1';
        /** @var \PHPUnit_Framework_MockObject_MockObject|Client $clientMock */
        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())
            ->method('request')
            ->with('_template/'.$name, Request::PUT, $args, [])
            ->willReturn($response);
        $indexTemplate = new IndexTemplate($clientMock, $name);
        $this->assertSame($response, $indexTemplate->create($args));
    }

    /**
     * @group unit
     */
    public function testExists()
    {
        $name = 'index_template1';
        $response = new Response('', 200);
        /** @var \PHPUnit_Framework_MockObject_MockObject|Client $clientMock */
        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())
            ->method('request')
            ->with('_template/'.$name, Request::HEAD, [], [])
            ->willReturn($response);
        $indexTemplate = new IndexTemplate($clientMock, $name);
        $this->assertTrue($indexTemplate->exists());
    }

    /**
     * @group functional
     */
    public function testCreateTemplate()
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

    /**
     * @group functional
     */
    public function testCreateAlreadyExistsTemplateException()
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
        } catch (ResponseException $ex) {
            $error = $ex->getResponse()->getFullError();

            $this->assertNotEquals('index_template_already_exists_exception', $error['type']);
            $this->assertEquals('resource_already_exists_exception', $error['type']);
            $this->assertEquals(400, $ex->getResponse()->getStatus());
        }
    }
}
