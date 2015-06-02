<?php
namespace Elastica\Transport;

use Elastica\Exception\Connection\HttpException;
use Elastica\Exception\PartialShardFailureException;
use Elastica\Exception\ResponseException;
use Elastica\JSON;
use Elastica\Request;
use Elastica\Response;

/**
 * Elastica Http Transport object.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Http extends AbstractTransport
{
    /**
     * Http scheme.
     *
     * @var string Http scheme
     */
    protected $_scheme = 'http';

    /**
     * Curl resource to reuse.
     *
     * @var resource Curl resource to reuse
     */
    protected static $_curlConnection = null;

    /**
     * Makes calls to the elasticsearch server.
     *
     * All calls that are made to the server are done through this function
     *
     * @param \Elastica\Request $request
     * @param array             $params  Host, Port, ...
     *
     * @throws \Elastica\Exception\ConnectionException
     * @throws \Elastica\Exception\ResponseException
     * @throws \Elastica\Exception\Connection\HttpException
     *
     * @return \Elastica\Response Response object
     */
    public function exec(Request $request, array $params)
    {
        $connection = $this->getConnection();

        $conn = $this->_getConnection($connection->isPersistent());

        // If url is set, url is taken. Otherwise port, host and path
        $url = $connection->hasConfig('url') ? $connection->getConfig('url') : '';

        if (!empty($url)) {
            $baseUri = $url;
        } else {
            $baseUri = $this->_scheme.'://'.$connection->getHost().':'.$connection->getPort().'/'.$connection->getPath();
        }

        $baseUri .= $request->getPath();

        $query = $request->getQuery();

        if (!empty($query)) {
            $baseUri .= '?'.http_build_query($query);
        }

        curl_setopt($conn, CURLOPT_URL, $baseUri);
        curl_setopt($conn, CURLOPT_TIMEOUT, $connection->getTimeout());
        curl_setopt($conn, CURLOPT_FORBID_REUSE, 0);

        /* @see Connection::setConnectTimeout() */
        $connectTimeout = $connection->getConnectTimeout();
        if ($connectTimeout > 0) {
            curl_setopt($conn, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
        }

        $proxy = $connection->getProxy();

        // See: https://github.com/facebook/hhvm/issues/4875
        if (is_null($proxy) && defined('HHVM_VERSION')) {
            $proxy = getenv('http_proxy') ?: null;
        }

        if (!is_null($proxy)) {
            curl_setopt($conn, CURLOPT_PROXY, $proxy);
        }

        $this->_setupCurl($conn);

        $headersConfig = $connection->hasConfig('headers') ? $connection->getConfig('headers') : array();

        if (!empty($headersConfig)) {
            $headers = array();
            while (list($header, $headerValue) = each($headersConfig)) {
                array_push($headers, $header.': '.$headerValue);
            }

            curl_setopt($conn, CURLOPT_HTTPHEADER, $headers);
        }

        // TODO: REFACTOR
        $data = $request->getData();
        $httpMethod = $request->getMethod();

        if (!empty($data) || '0' === $data) {
            if ($this->hasParam('postWithRequestBody') && $this->getParam('postWithRequestBody') == true) {
                $httpMethod = Request::POST;
            }

            if (is_array($data)) {
                $content = JSON::stringify($data, 'JSON_ELASTICSEARCH');
            } else {
                $content = $data;
            }

            // Escaping of / not necessary. Causes problems in base64 encoding of files
            $content = str_replace('\/', '/', $content);

            curl_setopt($conn, CURLOPT_POSTFIELDS, $content);
        } else {
            curl_setopt($conn, CURLOPT_POSTFIELDS, '');
        }

        curl_setopt($conn, CURLOPT_NOBODY, $httpMethod == 'HEAD');

        curl_setopt($conn, CURLOPT_CUSTOMREQUEST, $httpMethod);

        $start = microtime(true);

        // cURL opt returntransfer leaks memory, therefore OB instead.
        ob_start();
        curl_exec($conn);
        $responseString = ob_get_clean();

        $end = microtime(true);

        // Checks if error exists
        $errorNumber = curl_errno($conn);

        $response = new Response($responseString, $this->_curlGetInfo($conn, CURLINFO_HTTP_CODE));
        $response->setQueryTime($end - $start);

        $response->setTransferInfo($this->_curlGetInfo($conn));

        if ($response->hasError()) {
            throw new ResponseException($request, $response);
        }

        if ($response->hasFailedShards()) {
            throw new PartialShardFailureException($request, $response);
        }

        if ($errorNumber > 0) {
            throw new HttpException($errorNumber, $request, $response);
        }

        return $response;
    }

    /**
     * Called to add additional curl params.
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
     * Return Curl resource.
     *
     * @param bool $persistent False if not persistent connection
     *
     * @return resource Connection resource
     */
    protected function _getConnection($persistent = true)
    {
        if (!$persistent || !self::$_curlConnection) {
            self::$_curlConnection = curl_init();
        }

        return self::$_curlConnection;
    }

    /**
     * Return information about last request.
     *
     * @link https://github.com/ruflin/Elastica/issues/861
     *
     * @param resource $ch
     * @param int      $opt
     *
     * @return array
     */
    protected function _curlGetInfo($ch, $opt = null)
    {
        if (!empty($opt)) {
            return curl_getinfo($ch, $opt);
        }

        if (version_compare(phpversion(), 7, '<')) {
            return curl_getinfo($ch);
        }

        return array(
            'url' => curl_getinfo($ch, CURLINFO_EFFECTIVE_URL),
            'content_type' => curl_getinfo($ch, CURLINFO_CONTENT_TYPE),
            'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
            'header_size' => curl_getinfo($ch, CURLINFO_HEADER_SIZE),
            'request_size' => curl_getinfo($ch, CURLINFO_REQUEST_SIZE),
            'filetime' => curl_getinfo($ch, CURLINFO_FILETIME),
            'ssl_verify_result' => curl_getinfo($ch, CURLINFO_SSL_VERIFYRESULT),
            'redirect_count' => curl_getinfo($ch, CURLINFO_REDIRECT_COUNT),
            'total_time' => curl_getinfo($ch, CURLINFO_TOTAL_TIME),
            'namelookup_time' => curl_getinfo($ch, CURLINFO_NAMELOOKUP_TIME),
            'connect_time' => curl_getinfo($ch, CURLINFO_CONNECT_TIME),
            'pretransfer_time' => curl_getinfo($ch, CURLINFO_PRETRANSFER_TIME),
            'size_upload' => curl_getinfo($ch, CURLINFO_SIZE_UPLOAD),
            'size_download' => curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD),
            'speed_download' => curl_getinfo($ch, CURLINFO_SPEED_DOWNLOAD),
            'speed_upload' => curl_getinfo($ch, CURLINFO_SPEED_UPLOAD),
            'download_content_length' => curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD),
            'upload_content_length' => curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_UPLOAD),
            'starttransfer_time' => curl_getinfo($ch, CURLINFO_STARTTRANSFER_TIME),
            'redirect_time' => curl_getinfo($ch, CURLINFO_REDIRECT_TIME),
            'certinfo' => curl_getinfo($ch, CURLINFO_CERTINFO),
            'primary_ip' => curl_getinfo($ch, CURLINFO_PRIMARY_IP),
            'primary_port' => curl_getinfo($ch, CURLINFO_PRIMARY_PORT),
            'local_ip' => curl_getinfo($ch, CURLINFO_LOCAL_IP),
            'local_port' => curl_getinfo($ch, CURLINFO_LOCAL_PORT),
            'redirect_url' => curl_getinfo($ch, CURLINFO_REDIRECT_URL),
            'request_header' => curl_getinfo($ch, CURLINFO_HEADER_OUT),
        );
    }
}
