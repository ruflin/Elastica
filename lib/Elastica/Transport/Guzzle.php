<?php

namespace Elastica\Transport;

use Elastica\Connection;
use Elastica\Exception\Connection\GuzzleException;
use Elastica\Exception\PartialShardFailureException;
use Elastica\Exception\ResponseException;
use Elastica\JSON;
use Elastica\Request;
use Elastica\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Uri;

/**
 * Elastica Guzzle Transport object.
 *
 * @author Milan Magudia <milan@magudia.com>
 */
class Guzzle extends AbstractTransport
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
     * @var Client Guzzle client to reuse
     */
    protected static $_guzzleClientConnection = null;

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

        $client = $this->_getGuzzleClient($this->_getBaseUrl($connection), $connection->isPersistent());

        $options = array(
            'exceptions' => false, // 4xx and 5xx is expected and NOT an exceptions in this context
        );
        if ($connection->getTimeout()) {
            $options['timeout'] = $connection->getTimeout();
        }

        $proxy = $connection->getProxy();

        // See: https://github.com/facebook/hhvm/issues/4875
        if (is_null($proxy) && defined('HHVM_VERSION')) {
            $proxy = getenv('http_proxy') ?: null;
        }

        if (!is_null($proxy)) {
            $options['proxy'] = $proxy;
        }

        $req = $this->_createPsr7Request($request, $connection);

        try {
            $start = microtime(true);
            $res = $client->send($req, $options);
            $end = microtime(true);
        } catch (TransferException $ex) {
            throw new GuzzleException($ex, $request, new Response($ex->getMessage()));
        }

        $response = new Response((string) $res->getBody(), $res->getStatusCode());
        $response->setQueryTime($end - $start);
        if ($connection->hasConfig('bigintConversion')) {
            $response->setJsonBigintConversion($connection->getConfig('bigintConversion'));
        }

        $response->setTransferInfo(
            array(
                'request_header' => $request->getMethod(),
                'http_code' => $res->getStatusCode(),
            )
        );

        if ($response->hasError()) {
            throw new ResponseException($request, $response);
        }

        if ($response->hasFailedShards()) {
            throw new PartialShardFailureException($request, $response);
        }

        return $response;
    }

    /**
     * @param Request    $request
     * @param Connection $connection
     *
     * @return Psr7\Request
     */
    protected function _createPsr7Request(Request $request, Connection $connection)
    {
        $req = new Psr7\Request(
            $request->getMethod(),
            $this->_getActionPath($request),
            $connection->hasConfig('headers') && is_array($connection->getConfig('headers'))
                ? $connection->getConfig('headers')
                : array()
        );

        $data = $request->getData();
        if (!empty($data) || '0' === $data) {
            if ($req->getMethod() == Request::GET) {
                $req = $req->withMethod(Request::POST);
            }

            if ($this->hasParam('postWithRequestBody') && $this->getParam('postWithRequestBody') == true) {
                $request->setMethod(Request::POST);
                $req = $req->withMethod(Request::POST);
            }

            $req = $req->withBody(
                Psr7\stream_for(is_array($data)
                    ? JSON::stringify($data, 'JSON_ELASTICSEARCH')
                    : $data
                )
            );
        }

        return $req;
    }

    /**
     * Return Guzzle resource.
     *
     * @param bool $persistent False if not persistent connection
     *
     * @return Client
     */
    protected function _getGuzzleClient($baseUrl, $persistent = true)
    {
        if (!$persistent || !self::$_guzzleClientConnection) {
            self::$_guzzleClientConnection = new Client(array('base_uri' => $baseUrl));
        }

        return self::$_guzzleClientConnection;
    }

    /**
     * Builds the base url for the guzzle connection.
     *
     * @param \Elastica\Connection $connection
     *
     * @return string
     */
    protected function _getBaseUrl(Connection $connection)
    {
        // If url is set, url is taken. Otherwise port, host and path
        $url = $connection->hasConfig('url') ? $connection->getConfig('url') : '';

        if (!empty($url)) {
            $baseUri = $url;
        } else {
            $baseUri = (string) Uri::fromParts(array(
                'scheme' => $this->_scheme,
                'host' => $connection->getHost(),
                'port' => $connection->getPort(),
                'path' => ltrim('/', $connection->getPath()),
            ));
        }

        return rtrim($baseUri, '/');
    }

    /**
     * Builds the action path url for each request.
     *
     * @param \Elastica\Request $request
     *
     * @return string
     */
    protected function _getActionPath(Request $request)
    {
        $action = $request->getPath();
        if ($action) {
            $action = '/'.ltrim($action, '/');
        }
        $query = $request->getQuery();

        if (!empty($query)) {
            $action .= '?'.http_build_query($query);
        }

        return $action;
    }
}
