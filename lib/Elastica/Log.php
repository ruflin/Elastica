<?php
/**
 * Elastica log object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Log
{
	/**
	 * Log path or true if enabled
	 * 
	 * @var string|bool
	 */
	protected $_log = false;
	
	/**
	 * @var string Last logged message
	 */
	protected $_lastMessage = '';
	
	/**
	 * Inits log object. Checks if logging is enabled for the given client
	 * 
	 * @param Elastica_Client $client
	 */
	public function __construct(Elastica_Client $client) {
		$this->setLog($client->getConfig('log'));
	}
	
	/**
	 * @param string|Elastica_Request $message
	 */
	public function log($message) {
		if ($message instanceof Elastica_Request) {
			$message = $this->_convertRequest($message);
		}
		
		if ($this->_log) {
			$this->_lastMessage = $message;
			
			if (is_string($this->_log)) {
				error_log($message . PHP_EOL, 3, $this->_log);
			} else {
				error_log($message);
			}
		}
	}
	
	/**
	 * @param bool|string $log Enables log or sets log path
	 * @return Elastica_Log
	 */
	public function setLog($log) {
		$this->_log = $log;
		return $this;
	}
	
	/**
	 * Converts a request to a log message
	 * 
	 * @param Elastica_Request $request
	 * @return string Request log message
	 */
	protected function _convertRequest(Elastica_Request $request) {
		$message = 'curl -X' . strtoupper($request->getMethod()) . ' ';
		$message .= 'http://' . $request->getClient()->getHost() . ':' . $request->getClient()->getPort() . '/';
		$message .= $request->getPath();
		$message .= ' -d \'' . json_encode($request->getData()) . '\'';
		return $message;
	}
	
	/**
	 * @return string Last logged message
	 */
	public function getLastMessage() {
		return $this->_lastMessage;
	}
}