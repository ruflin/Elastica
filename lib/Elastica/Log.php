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
		if (!empty($this->_log)) {
			$this->_lastMessage = $message;
			error_log($message . PHP_EOL, 3, $this->_log);
		}
	}
	
	public function getLastMessage() {
		return $this->_lastMessage;
	}
}