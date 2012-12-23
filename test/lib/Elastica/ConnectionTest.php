<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

class Elastica_ConnectionTest extends Elastica_Test
{

	public function testEmptyConstructor()
	{
		$connection = new Elastica_Connection();
		$this->assertEquals(Elastica_Connection::DEFAULT_HOST, $connection->getHost());
		$this->assertEquals(Elastica_Connection::DEFAULT_PORT, $connection->getPort());
		$this->assertEquals(Elastica_Connection::DEFAULT_TRANSPORT, $connection->getTransport());
		$this->assertInstanceOf('Elastica_Transport_Abstract', $connection->getTransportObject());
		$this->assertEquals(Elastica_Connection::TIMEOUT, $connection->getTimeout());
		$this->assertTrue($connection->isEnabled());
	}

	public function testEnabledDisable() {
		$connection = new Elastica_Connection();
		$this->assertTrue($connection->isEnabled());
		$connection->setEnabled(false);
		$this->assertFalse($connection->isEnabled());
		$connection->setEnabled(true);
		$this->assertTrue($connection->isEnabled());
	}

	public function testInvalidConnection() {

		$connection = new Elastica_Connection(array('port' => 9202));

		$request = new Elastica_Request($connection, '_status', Elastica_Request::GET);
		$response = $request->send();
		//curl -XGET 'http://localhost:9200/_status'

		print_r($response);


	}
}
