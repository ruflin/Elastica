<?php
/**
 * Elastica File Transport object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * 2012/01/07 - Akhavi - Added error_logging
 */
class Elastica_Transport_File extends Elastica_Transport_Abstract {

	/**
	 * @var string File scheme
	 */
	protected $_scheme = 'http';

	/**
	 * @var resource Curl resource to reuse
	 */
	protected static $_connection = null;

	/**
	 * Makes calls to the elasticsearch server
	 *
	 * All calls that are made to the server are done through this function
	 *
	 * @param string $host Host name
	 * @param int $port Port number
	 * @return Elastica_Response Response object
	 */
	public function exec($host, $port) {
		$conn = $this->_getConnection();

		$request = $this->getRequest();

		$baseUri = $this->_scheme . '://' . $host . ':' . $port . '/';

		$baseUri .= $request->getPath();

		curl_setopt($conn, CURLOPT_URL, $baseUri);
		error_log($request->getMethod() . " " . $baseUri . "\n", 3, "/tmp/elastica.log");

		curl_setopt($conn, CURLOPT_TIMEOUT, $request->getConfig('timeout'));
		curl_setopt($conn, CURLOPT_PORT, $port);
		curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1) ;
		curl_setopt($conn, CURLOPT_CUSTOMREQUEST, $request->getMethod());
		curl_setopt($conn, CURLOPT_FORBID_REUSE, 0);
		
		$this->_setupCurl($conn);

		$headersConfig = $request->getConfig('headers');
		if (!empty($headersConfig)) {
			$headers = array();
			while (list($header, $headerValue) = each($headersConfig)) {
				array_push($headers, $header . ': ' . $headerValue);
			}

			curl_setopt($conn, CURLOPT_HTTPHEADER, $headers);
		}

		// TODO: REFACTOR
		$data = $request->getData();

		if (isset($data)) {
			if (is_array($data)) {
				$content = json_encode($data);
			} else {
				$content = $data;
			}

			// Escaping of / not necessary. Causes problems in base64 encoding of files
			$content = str_replace('\/', '/', $content);
			curl_setopt($conn, CURLOPT_POSTFIELDS, $content);
			error_log($content . "\n", 3, "/tmp/elastica.log");
		}

		$start = microtime(true);
		$responseString = curl_exec($conn);
		$end = microtime(true);

		// Checks if error exists
		$errorNumber = curl_errno($conn);

		$response = new Elastica_Response($responseString);

		if (defined('DEBUG') && DEBUG) {
			$response->setQueryTime($end - $start);
			$response->setTransferInfo(curl_getinfo($conn));
		}

		if ($response->hasError()) {
			throw new Elastica_Exception_Response($response);
		}

		if ($errorNumber > 0) {
			throw new Elastica_Exception_Client($errorNumber, $request, $response);
		}

		return $response;
	}
	
	/**
	 * Called to add additional curl params
	 * 
	 * @param resource $connection Curl connection
	 */
	protected function _setupCurl($connection) {
	}

	/**
	 * @return resource Connection resource
	 */
	protected function _getConnection() {
		if (!self::$_connection) {
			self::$_connection = curl_init();
		}

		return self::$_connection;
	}
}
