<?php

namespace Elastica\Transport;

use Elastica\Exception\Connection\HttpException;
use Elastica\Exception\ConnectionException;
use Elastica\Exception\PartialShardFailureException;
use Elastica\Exception\ResponseException;
use Elastica\JSON;
use Elastica\Request;
use Elastica\Response;
use Elastica\Util;

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
     * @var \CurlHandle|resource|null Curl resource to reuse
     */
    protected static $_curlConnection;

    /**
     * Makes calls to the elasticsearch server.
     *
     * All calls that are made to the server are done through this function
     *
     * @param array<string, mixed> $params Host, Port, ...
     *
     * @throws ConnectionException
     * @throws ResponseException
     * @throws HttpException
     *
     * @return Response Response object
     */
    public function exec(Request $request, array $params): Response
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

        $requestPath = $request->getPath();
        if (!Util::isDateMathEscaped($requestPath)) {
            $requestPath = Util::escapeDateMath($requestPath);
        }

        $baseUri .= $requestPath;

        $query = $request->getQuery();

        if (!empty($query)) {
            $baseUri .= '?'.\http_build_query(
                $this->sanityzeQueryStringBool($query)
            );
        }

        \curl_setopt($conn, \CURLOPT_URL, $baseUri);
        \curl_setopt($conn, \CURLOPT_TIMEOUT_MS, $connection->getTimeout() * 1000);
        \curl_setopt($conn, \CURLOPT_FORBID_REUSE, 0);

        // Tell ES that we support the compressed responses
        // An "Accept-Encoding" header containing all supported encoding types is sent
        // curl will decode the response automatically if the response is encoded
        \curl_setopt($conn, \CURLOPT_ENCODING, '');

        /* @see Connection::setConnectTimeout() */
        $connectTimeoutMs = $connection->getConnectTimeout() * 1000;

        // Let's only apply this value if the number of ms is greater than or equal to "1".
        // In case "0" is passed as an argument, the value is reset to its default (300 s)
        if ($connectTimeoutMs >= 1) {
            \curl_setopt($conn, \CURLOPT_CONNECTTIMEOUT_MS, $connectTimeoutMs);
        }

        if (null !== $proxy = $connection->getProxy()) {
            \curl_setopt($conn, \CURLOPT_PROXY, $proxy);
        }

        $username = $connection->getUsername();
        $password = $connection->getPassword();
        if (null !== $username && null !== $password) {
            \curl_setopt($conn, \CURLOPT_HTTPAUTH, $this->_getAuthType());
            \curl_setopt($conn, \CURLOPT_USERPWD, "{$username}:{$password}");
        }

        $this->_setupCurl($conn);

        $headersConfig = $connection->hasConfig('headers') ? $connection->getConfig('headers') : [];

        $headers = [];

        if (!empty($headersConfig)) {
            foreach ($headersConfig as $header => $headerValue) {
                $headers[] = $header.': '.$headerValue;
            }
        }

        // TODO: REFACTOR
        $data = $request->getData();
        $httpMethod = $request->getMethod();

        $headers[] = 'Content-Type: '.$request->getContentType();

        if (!empty($data)) {
            if ($this->hasParam('postWithRequestBody') && true == $this->getParam('postWithRequestBody')) {
                $httpMethod = Request::POST;
            }

            if (\is_array($data)) {
                $content = JSON::stringify($data, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
            } else {
                $content = $data;

                // Escaping of / not necessary. Causes problems in base64 encoding of files
                $content = \str_replace('\/', '/', $content);
            }

            if ($connection->hasCompression()) {
                // Compress the body of the request ...
                \curl_setopt($conn, \CURLOPT_POSTFIELDS, \gzencode($content));

                // ... and tell ES that it is compressed
                $headers[] = 'Content-Encoding: gzip';
            } else {
                \curl_setopt($conn, \CURLOPT_POSTFIELDS, $content);
            }
        } else {
            \curl_setopt($conn, \CURLOPT_POSTFIELDS, '');
        }

        \curl_setopt($conn, \CURLOPT_HTTPHEADER, $headers);

        \curl_setopt($conn, \CURLOPT_NOBODY, 'HEAD' === $httpMethod);

        \curl_setopt($conn, \CURLOPT_CUSTOMREQUEST, $httpMethod);

        $start = \microtime(true);

        // cURL opt returntransfer leaks memory, therefore OB instead.
        \ob_start();
        \curl_exec($conn);
        $responseString = \ob_get_clean();

        $end = \microtime(true);

        // Checks if error exists
        $errorNumber = \curl_errno($conn);

        $response = new Response($responseString, \curl_getinfo($conn, \CURLINFO_RESPONSE_CODE));
        $response->setQueryTime($end - $start);
        $response->setTransferInfo(\curl_getinfo($conn));
        if ($connection->hasConfig('bigintConversion')) {
            $response->setJsonBigintConversion($connection->getConfig('bigintConversion'));
        }

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
     * @param \CurlHandle|resource $curlConnection Curl connection
     */
    protected function _setupCurl($curlConnection): void
    {
        if ($this->getConnection()->hasConfig('curl')) {
            foreach ($this->getConnection()->getConfig('curl') as $key => $param) {
                \curl_setopt($curlConnection, $key, $param);
            }
        }
    }

    /**
     * Return Curl resource.
     *
     * @param bool $persistent False if not persistent connection
     *
     * @return \CurlHandle|resource Connection resource
     */
    protected function _getConnection(bool $persistent = true)
    {
        if (!$persistent || !self::$_curlConnection) {
            self::$_curlConnection = \curl_init();
        }

        return self::$_curlConnection;
    }

    /**
     * @return int
     */
    protected function _getAuthType()
    {
        switch ($this->_connection->getAuthType()) {
            case 'digest':
                return \CURLAUTH_DIGEST;
            case 'gssnegotiate':
                return \CURLAUTH_GSSNEGOTIATE;
            case 'ntlm':
                return \CURLAUTH_NTLM;
            case 'basic':
                return \CURLAUTH_BASIC;
            default:
                return \CURLAUTH_ANY;
        }
    }
}
