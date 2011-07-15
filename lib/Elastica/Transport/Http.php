<?php
/**
 * Elastica Http Transport object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Transport_Http extends Elastica_Transport_Abstract {

	/**
	 * Makes calls to the elasticsearch server
	 *
	 * All calls that are made to the server are done through this function
	 *
	 *@param string $host Host name
	 * @param int $port Port number
	 * @return Elastica_Response Response object
	 */
	public function exec($host, $port) {
		$conn = curl_init();

		$request = $this->getRequest();

		$baseUri = 'http://' . $host . ':' . $port . '/';

		$baseUri .= $request->getPath();

		curl_setopt($conn, CURLOPT_URL, $baseUri);
		curl_setopt($conn, CURLOPT_TIMEOUT, $request->getConfig('timeout'));
		curl_setopt($conn, CURLOPT_PORT, $port);
		curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1) ;
		curl_setopt($conn, CURLOPT_CUSTOMREQUEST, $request->getMethod());

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

		if (!empty($data)) {
			if (is_array($data)) {
				$content = json_encode($data);
			} else {
				$content = $data;
			}

			// Escaping of / not necessary. Causes problems in base64 encoding of files
			$content = str_replace('\/', '/', $content);
			curl_setopt($conn, CURLOPT_POSTFIELDS, $content);
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
}