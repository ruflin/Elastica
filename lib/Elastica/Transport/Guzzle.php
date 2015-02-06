<?php

namespace Elastica\Transport;

use Elastica\Connection;
use Elastica\Exception\Connection\GuzzleException;
use Elastica\Exception\Connection\HttpException;
use Elastica\Exception\PartialShardFailureException;
use Elastica\Exception\ResponseException;
use Elastica\JSON;
use Elastica\Request;
use Elastica\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Stream\Stream;

/**
 * Elastica Guzzle Transport object
 *
 * @package Elastica
 * @author Milan Magudia <milan@magudia.com>
 */
class Guzzle extends AbstractTransport
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
     * @var resource Guzzle resource to reuse
     */
    protected static $_guzzleClientConnection = null;

    /**
     * Makes calls to the elasticsearch server
     *
     * All calls that are made to the server are done through this function
     *
     * @param  \Elastica\Request                            $request
     * @param  array                                        $params  Host, Port, ...
     * @throws \Elastica\Exception\ConnectionException
     * @throws \Elastica\Exception\ResponseException
     * @throws \Elastica\Exception\Connection\HttpException
     * @return \Elastica\Response                           Response object
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
        if (!is_null($proxy)) {
            $options['proxy'] = $proxy;
        }

        $req = $client->createRequest($request->getMethod(), $this->_getActionPath($request), $options);
        $req->setHeaders($connection->hasConfig('headers') ? $connection->getConfig('headers') : array());

        $data = $request->getData();
        if (!empty($data) || '0' === $data) {
            if ($req->getMethod() == Request::GET) {
                $req->setMethod(Request::POST);
            }

            if ($this->hasParam('postWithRequestBody') && $this->getParam('postWithRequestBody') == true) {
                $request->setMethod(Request::POST);
                $req->setMethod(Request::POST);
            }

            if (is_array($data)) {
                $content = JSON::stringify($data, 'JSON_ELASTICSEARCH');
            } else {
                $content = $data;
            }
            $req->setBody(Stream::factory($content));
        }

        try {
            $start = microtime(true);
            $res = $client->send($req);
            $end = microtime(true);
        } catch (TransferException $ex) {
            throw new GuzzleException($ex, $request, new Response($ex->getMessage()));
        }

        $response = new Response((string) $res->getBody(), $res->getStatusCode());

        if (defined('DEBUG') && DEBUG) {
            $response->setQueryTime($end - $start);
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
     * Return Guzzle resource
     *
     * @param  bool     $persistent False if not persistent connection
     * @return resource Connection resource
     */
    protected function _getGuzzleClient($baseUrl, $persistent = true)
    {
        if (!$persistent || !self::$_guzzleClientConnection) {
            self::$_guzzleClientConnection = new Client(array('base_url' => $baseUrl));
        }

        return self::$_guzzleClientConnection;
    }

    /**
     * Builds the base url for the guzzle connection
     *
     * @param \Elastica\Connection $connection
     */
    protected function _getBaseUrl(Connection $connection)
    {
        // If url is set, url is taken. Otherwise port, host and path
        $url = $connection->hasConfig('url') ? $connection->getConfig('url') : '';

        if (!empty($url)) {
            $baseUri = $url;
        } else {
            $baseUri = $this->_scheme.'://'.$connection->getHost().':'.$connection->getPort().'/'.$connection->getPath();
        }

        return rtrim($baseUri, '/');
    }

    /**
     * Builds the action path url for each request
     *
     * @param \Elastica\Request $request
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
