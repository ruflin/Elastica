<?php
namespace Elastica\Test;

use Elastica\Client;
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
     * @expectedException \Elastica\Exception\InvalidException
     * @group unit
     */
    public function testIncorrectInstantiate()
    {
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
        $clientMock = $this->getMock('\Elastica\Client', array('request'));
        $clientMock->expects($this->once())
            ->method('request')
            ->with('/_template/'.$name, Request::DELETE, array(), array())
            ->willReturn($response);
        $indexTemplate = new IndexTemplate($clientMock, $name);
        $this->assertSame($response, $indexTemplate->delete());
    }

    /**
     * @group unit
     */
    public function testCreate()
    {
        $args = array(1);
        $response = new Response('');
        $name = 'index_template1';
        /** @var \PHPUnit_Framework_MockObject_MockObject|Client $clientMock */
        $clientMock = $this->getMock('\Elastica\Client', array('request'));
        $clientMock->expects($this->once())
            ->method('request')
            ->with('/_template/'.$name, Request::PUT, $args, array())
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
        $response = new Response('');
        $response->setTransferInfo(array('http_code' => 200));
        /** @var \PHPUnit_Framework_MockObject_MockObject|Client $clientMock */
        $clientMock = $this->getMock('\Elastica\Client', array('request'));
        $clientMock->expects($this->once())
            ->method('request')
            ->with('/_template/'.$name, Request::HEAD, array(), array())
            ->willReturn($response);
        $indexTemplate = new IndexTemplate($clientMock, $name);
        $this->assertTrue($indexTemplate->exists());
    }

    /**
     * @group functional
     */
    public function testCreateTemplate()
    {
        $template = array(
            'template' => 'te*',
            'settings' => array(
                'number_of_shards' => 1,
            ),
        );
        $name = 'index_template1';
        $indexTemplate = new IndexTemplate($this->_getClient(), $name);
        $indexTemplate->create($template);
        $this->assertTrue($indexTemplate->exists());
        $indexTemplate->delete();
        $this->assertFalse($indexTemplate->exists());
    }
}
