<?php

namespace Elastica\Test\Transport;

use Elastica\Connection;
use Elastica\Transport\AbstractTransport;
use Elastica\Transport\Http;

class AbstractTransportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Return transport configuration and the expected HTTP method.
     *
     * @return array[]
     */
    public function getValidDefinitions()
    {
        $connection = new Connection();

        return array(
            array('Http'),
            array(array('type' => 'Http')),
            array(array('type' => new Http())),
            array(new Http()),
        );
    }

    /**
     * @group unit
     * @dataProvider getValidDefinitions
     */
    public function testCanCreateTransportInstances($transport)
    {
        $connection = new Connection();
        $params = array();
        $transport = AbstractTransport::create($transport, $connection, $params);
        $this->assertInstanceOf('Elastica\Transport\AbstractTransport', $transport);
        $this->assertSame($connection, $transport->getConnection());
    }

    public function getInvalidDefinitions()
    {
        return array(
            array(array('transport' => 'Http')),
            array('InvalidTransport'),
        );
    }

    /**
     * @group unit
     * @dataProvider getInvalidDefinitions
     * @expectedException Elastica\Exception\InvalidException
     * @expectedExceptionMessage Invalid transport
     */
    public function testThrowsExecptionOnInvalidTransportDefinition($transport)
    {
        AbstractTransport::create($transport, new Connection());
    }

    /**
     * @group unit
     */
    public function testCanInjectParamsWhenUsingArray()
    {
        $connection = new Connection();
        $params = array(
            'param1' => 'some value',
            'param3' => 'value3',
        );

        $transport = AbstractTransport::create(array(
            'type' => 'Http',
            'param1' => 'value1',
            'param2' => 'value2',
        ), $connection, $params);

        $this->assertSame('value1', $transport->getParam('param1'));
        $this->assertSame('value2', $transport->getParam('param2'));
        $this->assertSame('value3', $transport->getParam('param3'));
    }
}
