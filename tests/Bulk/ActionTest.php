<?php

namespace Elastica\Test\Bulk;

use Elastica\Bulk\Action;
use Elastica\Index;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class ActionTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testAction(): void
    {
        $action = new Action();
        $this->assertEquals('index', $action->getOpType());
        $this->assertFalse($action->hasSource());

        $expected = '{"index":{}}'."\n";
        $this->assertEquals($expected, $action->toString());

        $action->setIndex('index');
        $expected = '{"index":{"_index":"index"}}'."\n";
        $this->assertEquals($expected, $action->toString());

        $action->setId(1);
        $expected = '{"index":{"_index":"index","_id":"1"}}'."\n";
        $this->assertEquals($expected, $action->toString());

        $action->setRouting(1);
        $expected = '{"index":{"_index":"index","_id":"1","routing":1}}'."\n";
        $this->assertEquals($expected, $action->toString());

        $client = $this->_getClient();
        $index = new Index($client, 'index2');
        $action->setIndex($index);

        $expected = '{"index":{"_index":"index2","_id":"1","routing":1}}'."\n";
        $this->assertEquals($expected, $action->toString());

        $action->setSource(['user' => 'name']);
        $expected = '{"index":{"_index":"index2","_id":"1","routing":1}}'."\n";
        $expected .= '{"user":"name"}'."\n";
        $this->assertEquals($expected, $action->toString());
        $this->assertTrue($action->hasSource());
        $this->assertFalse(Action::isValidOpType('foo'));
        $this->assertTrue(Action::isValidOpType('delete'));
    }
}
