<?php
/**
 * Elastica Http Transport object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Transport_Http extends Elastica_Transport_Abstract
{
    /**
     * Http scheme
     *
     * @var string Http scheme
     */
    protected $_scheme = 'http';

    /**
     * Curl resource to reuse
     *
     * @var resource Curl resource to reuse
     */
    protected static $_connection = null;

    /**
     * Makes calls to the elasticsearch server
     *
     * All calls that are made to the server are done through this function
     *
     * @param  array             $params Host, Port, ...
     * @return Elastica_Response Response object
     */
    public function exec(array $params)
    {
        $request = $this->getRequest();

        $conn = $this->_getConnection($request->getConfig('persistent'));

        // If url is set, url is taken. Otherwise port, host and path
        if (!empty($params['url'])) {
            $baseUri = $params['url'];
        } else {
            if (!isset($params['host']) || !isset($params['port'])) {
                throw new Elastica_Exception_Invalid('host and port have to be set');
            }

            $path = isset($params['path']) ? $params['path'] : '';

            $baseUri = $this->_scheme . '://' . $params['host'] . ':' . $params['port'] . '/' . $path;
        }

        $baseUri .= $request->getPath();

        $query = $request->getQuery();

        if (!empty($query)) {
            $baseUri .= '?' . http_build_query($query);
        }

        curl_setopt($conn, CURLOPT_URL, $baseUri);
        curl_setopt($conn, CURLOPT_TIMEOUT, $request->getConfig('timeout'));
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

        if (isset($data) && !empty($data)) {
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

        // cURL opt returntransfer leaks memory, therefore OB instead.
        ob_start();
        curl_exec($conn);
        $responseString = ob_get_clean();

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
    protected function _setupCurl($connection)
    {
        foreach ($this->_request->getClient()->getConfig('curl') as $key => $param) {
            curl_setopt($connection, $key, $param);
        }
    }

    /**
     * Return Curl ressource
     *
     * @param  bool     $persistent False if not persistent connection
     * @return resource Connection resource
     */
    protected function _getConnection($persistent = true)
    {
        if (!$persistent || !self::$_connection) {
            self::$_connection = curl_init();
        }

        return self::$_connection;
    }
}
