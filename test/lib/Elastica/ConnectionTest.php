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

	/**
	 * @expectedException Elastica_Exception_Connection
	 */
	public function testInvalidConnection() {

		$connection = new Elastica_Connection(array('port' => 9202));

		$request = new Elastica_Request('_status', Elastica_Request::GET);
		$request->setConnection($connection);

		// Throws exception because no valid connection
		$request->send();
	}

	public function testCreate() {
		$connection = Elastica_Connection::create();
		$this->assertInstanceOf('Elastica_Connection', $connection);

		$connection = Elastica_Connection::create(array());
		$this->assertInstanceOf('Elastica_Connection', $connection);

		$port = 9999;
		$connection = Elastica_Connection::create(array('port' => $port));
		$this->assertInstanceOf('Elastica_Connection', $connection);
		$this->assertEquals($port, $connection->getPort());
	}

	/**
	 * @expectedException Elastica_Exception_Invalid
	 */
	public function testCreateInvalid() {
		Elastica_Connection::create('test');
	}
}