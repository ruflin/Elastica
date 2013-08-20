<?php

namespace Elastica\Transport;

use Elastica\Exception\Connection\HttpException;
use Elastica\Exception\ResponseException;
use Elastica\Request;
use Elastica\Response;

/**
 * Elastica Http Transport object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Http extends AbstractTransport
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
    protected static $_curlConnection = null;

    /**
     * Makes calls to the elasticsearch server
     *
     * All calls that are made to the server are done through this function
     *
     * @param  \Elastica\Request                     $request
     * @param  array                                $params  Host, Port, ...
     * @throws \Elastica\Exception\ConnectionException
     * @throws \Elastica\Exception\ResponseException
     * @throws \Elastica\Exception\Connection\HttpException
     * @return \Elastica\Response                    Response object
     */
    public function exec(Request $request, array $params)
    {
        $connection = $this->getConnection();

        $conn = $this->_getConnection($connection->isPersistent());

        // If url is set, url is taken. Otherwise port, host and path
        $url = $connection->hasConfig('url')?$connection->getConfig('url'):'';

        if (!empty($url)) {
            $baseUri = $url;
        } else {
            $baseUri = $this->_scheme . '://' . $connection->getHost() . ':' . $connection->getPort() . '/' . $connection->getPath();
        }

        $baseUri .= $request->getPath();

        $query = $request->getQuery();

        if (!empty($query)) {
            $baseUri .= '?' . http_build_query($query);
        }

        curl_setopt($conn, CURLOPT_URL, $baseUri);
        curl_setopt($conn, CURLOPT_TIMEOUT, $connection->getTimeout());
        curl_setopt($conn, CURLOPT_FORBID_REUSE, 0);

        $proxy = $connection->getProxy();
        if (!is_null($proxy)) {
            curl_setopt($conn, CURLOPT_PROXY, $proxy);
        }

        $this->_setupCurl($conn);

        $headersConfig = $connection->hasConfig('headers')?$connection->getConfig('headers'):array();

        if (!empty($headersConfig)) {
            $headers = array();
            while (list($header, $headerValue) = each($headersConfig)) {
                array_push($headers, $header . ': ' . $headerValue);
            }

            curl_setopt($conn, CURLOPT_HTTPHEADER, $headers);
        }

        // TODO: REFACTOR
        $data = $request->getData();
        $httpMethod = $request->getMethod();

        if (isset($data) && !empty($data)) {
            if ($this->hasParam('postWithRequestBody') && $this->getParam('postWithRequestBody') == true) {
                $httpMethod = Request::POST;
            }

            if (is_array($data)) {
                $content = json_encode($data);
            } else {
                $content = $data;
            }

            // Escaping of / not necessary. Causes problems in base64 encoding of files
            $content = str_replace('\/', '/', $content);

            curl_setopt($conn, CURLOPT_POSTFIELDS, $content);
        }

        curl_setopt($conn, CURLOPT_NOBODY, $httpMethod == 'HEAD');

        curl_setopt($conn, CURLOPT_CUSTOMREQUEST, $httpMethod);

        if (defined('DEBUG') && DEBUG) {
            // Track request headers when in debug mode
            curl_setopt($conn, CURLINFO_HEADER_OUT, true);
        }

        $start = microtime(true);

        // cURL opt returntransfer leaks memory, therefore OB instead.
        ob_start();
        curl_exec($conn);
        $responseString = ob_get_clean();

        $end = microtime(true);

        // Checks if error exists
        $errorNumber = curl_errno($conn);

        $response = new Response($responseString);

        if (defined('DEBUG') && DEBUG) {
            $response->setQueryTime($end - $start);
        }

        $response->setTransferInfo(curl_getinfo($conn));


        if ($response->hasError()) {
            throw new ResponseException($request, $response);
        }

        if ($errorNumber > 0) {
            throw new HttpException($errorNumber, $request, $response);
        }

        return $response;
    }

    /**
     * Called to add additional curl params
     *
     * @param resource $curlConnection Curl connection
     */
    protected function _setupCurl($curlConnection)
    {
        if ($this->getConnection()->hasConfig('curl')) {
            foreach ($this->getConnection()->getConfig('curl') as $key => $param) {
                curl_setopt($curlConnection, $key, $param);
            }
        }
    }

    /**
     * Return Curl resource
     *
     * @param  bool     $persistent False if not persistent connection
     * @return resource Connection resource
     */
    protected function _getConnection($persistent = true)
    {
        if (!$persistent || !self::$_curlConnection) {
            self::$_curlConnection = curl_init();
        }

        return self::$_curlConnection;
    }
}
