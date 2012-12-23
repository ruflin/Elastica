<?php
/**
 * Elastica connection instance to an elasticasearch node
 *
 * @category Xodoa
 * @package  Elastica
 * @author   Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Connection extends Elastica_Param
{
	/**
	 * Default elastic search port
	 */
	const DEFAULT_PORT = 9200;

	/**
	 * Default host
	 */
	const DEFAULT_HOST = 'localhost';

	/**
	 * Default transport
	 *
	 * @var string
	 */
	const DEFAULT_TRANSPORT = 'Http';

	/**
	 * Number of seconds after a timeout occurs for every request
	 * If using indexing of file large value necessary.
	 */
	const TIMEOUT = 300;

	/**
	 * Creates a new connection object. A connection is enabled by default
	 *
	 * @param array $params OPTIONAL Connection params: host, port, transport, timeout. All are optional
	 */
	public function __construct(array $params = array()) {
		$this->setParams($params);
		$this->setEnabled(true);

		// Set empty config param if not exists
		if (!$this->hasParam('config')) {
			$this->setParam('config', array());
		}
	}

	/**
	 * @return int Server port
	 */
	public function getPort() {
		return $this->hasParam('port')?$this->getParam('port'):self::DEFAULT_PORT;
	}

	/**
	 * @param int $port
	 * @return Elastica_Connection
	 */
	public function setPort($port)
	{
		return $this->setParam('port', (int) $port);
	}

	/**
	 * @return string Host
	 */
	public function getHost()
	{
		return $this->hasParam('host')?$this->getParam('host'):self::DEFAULT_HOST;
	}

	/**
	 * @param string $host
	 * @return Elastica_Connection
	 */
	public function setHost($host)
	{
		return $this->setParam('host', $host);
	}

	/**
	 * @return string
	 */
	public function getTransport()
	{
		return $this->hasParam('transport')?$this->getParam('transport'):self::DEFAULT_TRANSPORT;
	}

	/**
	 * @param string $transport
	 * @return Elastica_Connection
	 */
	public function setTransport($transport)
	{
		return $this->setParam('transport', $transport);
	}

	/**
	 * @return string
	 */
	public function getPath()
	{
		return $this->hasParam('path')?$this->getParam('path'):'';
	}

	/**
	 * @param string $path
	 * @return Elastica_Connection
	 */
	public function setPath($path)
	{
		return $this->setParam('path', $path);
	}

	/**
	 * @param int $timeout Timeout in seconds
	 * @return Elastica_Connection
	 */
	public function setTimeout($timeout) {
		return $this->setParam('timeout', $timeout);
	}

	/**
	 * @return int Connection timeout in seconds
	 */
	public function getTimeout() {
		return (int)  $this->hasParam('timeout')?$this->getParam('timeout'):self::TIMEOUT;
	}

	/**
	 * Enables a connection
	 *
	 * @param bool $enabled OPTIONAL (default = true)
	 * @return Elastica_Connection
	 */
	public function setEnabled($enabled = true)
	{
		return $this->setParam('enabled', $enabled);
	}

	/**
	 * @return bool True if enabled
	 */
	public function isEnabled()
	{
		return (bool) $this->getParam('enabled');
	}

	/**
	 * Returns an instance of the transport type
	 *
	 * @return Elastica_Transport_Abstract Transport object
	 * @throws Elastica_Exception_Invalid  If invalid transport type
	 */
	public function getTransportObject()
	{
		$className = 'Elastica_Transport_' . $this->getTransport();
		if (!class_exists($className)) {
			throw new Elastica_Exception_Invalid('Invalid transport');
		}

		return new $className($this);
	}

	/**
	 * @return bool Returns true if connection is persistent. True by default
	 */
	public function isPersistent() {
		return (bool) $this->hasParam('persistent')?$this->getParam('persistent'):true;
	}

	public function setConfig(array $config) {
		return $this->setParam('config', $config);
	}

	public function addConfig($key, $value) {
		$this->_params['config'][$key] = $value;
		return $this;
	}

	public function hasConfig($key) {
		$config = $this->getConfig();
		return isset($config['key']);
	}

	/**
	 * Returns a specific config key or the whole
	 * config array if not set
	 *
	 * @param  string       $key Config key
	 * @throws Elastica_Exception_Invalid
	 * @return array|string Config value
	 */
	public function getConfig($key = '')
	{
		$config = $this->getParam('config');
		if (empty($key)) {
			return $config;
		}

		if (!array_key_exists($key, $config[$key])) {
			throw new Elastica_Exception_Invalid('Config key is not set: ' . $key);
		}

		return $this->$config[$key];
	}

	/**
	 * @param Elastica_Connection|array $params Params to create a connection
	 * @return Elastica_Connection
	 */
	public static function create($params = array()) {
		$connection = null;

		if ($params instanceof Elastica_Connection) {
			$connection = $params;
		} else if (is_array($params)) {
			$connection = new Elastica_Connection($params);
		} else {
			throw new Elastica_Exception_Invalid('Invalid data type');
		}

		return $connection;
	}
}