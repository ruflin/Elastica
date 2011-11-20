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
	protected $_log = '';
	protected $_lastMessage = '';
	
	public function __construct(Elastica_Client $client) {
		$this->_log = $client->getConfig('log');
	}
	
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
	
	protected function _convertRequest(Elastica_Request $request) {
		$message = 'curl -X' . strtoupper($request->getMethod()) . ' ';
		$message .= 'http://' . $request->getClient()->getHost() . ':' . $request->getClient()->getPort() . '/';
		$message .= $request->getPath();
		$message .= ' -d \'' . json_encode($request->getData()) . '\'';
		return $message;
	}
	
	public function getLastMessage() {
		return $this->_lastMessage;
	}
}