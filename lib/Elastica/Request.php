<?php
/**
 * Elastica Request object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Request {

	const POST = 'POST';
	const PUT = 'PUT';
	const GET = 'GET';
	const DELETE = 'DELETE';

	protected $_client;
	protected $_path;
	protected $_method;
	protected $_data;

	/**
	 * Internal id of last used server. This is used for round robin
	 *
	 * @var int Last server id
	 */
	protected static $_serverId = null;

	/**
	 * @param Elastica_Client $client
	 * @param string $path Request path
	 * @param string $method Request method (use const's)
	 * @param array $data Data array
	 */
	public function __construct(Elastica_Client $client, $path, $method, $data = array()) {
		$this->_client = $client;
		$this->_path = $path;
		$this->_method = $method;
		$this->_data = $data;
	}

	/**
	 * Sets the request method. Use one of the for consts
	 *
	 * @param string $method Request method
	 * @return Elastica_Request Current object
	 */
	public function setMethod($method) {
		$this->_method = $method;
		return $this;
	}

	/**
	 * @return string Request method
	 */
	public function getMethod() {
		return $this->_method;
	}

	/**
	 * Sets the request data
	 *
	 * @param array $data Request data
	 */
	public function setData($data) {
		$this->_data = $data;
		return $this;
	}

	/**
	 * @return array Request data
	 */
	public function getData() {
		return $this->_data;
	}

	/**
	 * Sets the request path
	 *
	 * @param string $path Request path
	 * @return Elastica_Request Current object
	 */
	public function setPath($path) {
		$this->_path = $path;
		return $this;
	}

	/**
	 * @return string Request path
	 */
	public function getPath() {
		return $this->_path;
	}

	/**
	 * @return Elastica_Client
	 */
	public function getClient() {
		return $this->_client;
	}

	/**
	 * Returns a specific config key or the whole
	 * config array if not set
	 *
	 * @param string $key Config key
	 * @return array|string Config value
	 */
	public function getConfig($key = '') {
		return $this->getClient()->getConfig($key);
	}

	/**
	 * Returns an instance of the transport type
	 *
	 * @return Elastica_Transport_Abstract Transport object
	 * @throws Elastica_Exception_Invalid If invalid transport type
	 */
	public function getTransport() {
		$className = 'Elastica_Transport_' . $this->_client->getConfig('transport');
		if (!class_exists($className)) {
			throw new Elastica_Exception_Invalid('Invalid transport');
		}

		return new $className($this);
	}

	/**
	 * Sends request to server
	 *
	 * @return Elastica_Response Response object
	 */
	public function send() {
		
		$transport = $this->getTransport();

		$servers = $this->getClient()->getConfig('servers');
		
		/*$dir = sys_get_temp_dir();
		$name = 'elasticaServers.json';
		$file = $dir . DIRECTORY_SEPARATOR . $name;
		
		error_log($file);

		if (!file_exists($file)) {
			file_put_contents($file, 'hh');
			error_log(print_r($this->getClient()->getCluster(), true));
		}*/
		
		
		if (empty($servers)) {
			$response = $transport->exec($this->getClient()->getHost(), $this->getClient()->getPort());
		} else {
			
	
			// Set server id for first request (round robin by default)
			if (is_null(self::$_serverId)) {
				self::$_serverId = rand(0, count($servers) - 1);
			} else {
				self::$_serverId = (self::$_serverId + 1) % count($servers);
			}

			$server = $servers[self::$_serverId];

			$response = $transport->exec($server['host'], $server['port']);
		}
		
		$log = new Elastica_Log($this->getClient());
		$log->log($this);

		return $response;
	}
}