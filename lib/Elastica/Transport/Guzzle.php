<?php

namespace Elastica\Transport;

use Elastica\Exception\Connection\GuzzleException;
use Elastica\Exception\PartialShardFailureException;
use Elastica\Exception\ResponseException;
use Elastica\Connection;
use Elastica\Request;
use Elastica\Response;
use Elastica\JSON;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Promise\Promise;
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
     * @var Client
     */
    protected static $_guzzleClientConnection = null;

    /**
     * Makes calls to the elasticsearch server
     *
     * All calls that are made to the server are done through this function
     *
     * @param  \Elastica\Request $request
     * @param  array $params Host, Port, ...
     * @throws \Elastica\Exception\ConnectionException
     * @throws \Elastica\Exception\ResponseException
     * @throws \Elastica\Exception\Connection\HttpException
     * @return \Elastica\Response                    Response object
     */
    public function exec(Request $request, array $params)
    {
        $connection = $this->getConnection();

        try {
            /** @var Client $client */
            $client = $this->_getGuzzleClient($this->_getBaseUrl($connection), $connection->isPersistent());

            $options = array();
            if ($connection->getTimeout()) {
                $options['timeout'] = $connection->getTimeout();
            }

            if ($connection->getProxy()) {
                $options['proxy'] = $connection->getProxy();
            }

            $requestMethod = $request->getMethod();
            $data = $request->getData();
            if (isset($data) && !empty($data)) {
                // if there's any post data, set the request method to be post
                if ($request->getMethod() == Request::GET) {
                    $requestMethod = Request::POST;
                }

                if ($this->hasParam('postWithRequestBody') && $this->getParam('postWithRequestBody') == true) {
                    $request->setMethod(Request::POST);
                    $requestMethod = Request::POST;
                }

                if (is_array($data)) {
                    $content = JSON::stringify($data, 'JSON_ELASTICSEARCH');
                } else {
                    $content = $data;
                }
                $options['body'] = $content;
            }
            $options['headers'] = $connection->hasConfig('headers') ? $connection->getConfig('headers'): array();

            $start = microtime(true);
            $coRoutine = \Icicle\Coroutine\create(function() use ($client, $requestMethod, $connection, $request, $options) {
                try {
                    /** @var Promise $promise */
                    $promise = (yield \Icicle\Awaitable\resolve($client->requestAsync($requestMethod, $this->_getBaseUrl($connection) . $this->_getActionPath($request), $options)));
                    yield \Icicle\Awaitable\resolve($promise->wait(true));
                } catch (\Exception $e) {
                    yield \Icicle\Awaitable\reject($e);
                }
            });
            $res = $coRoutine->wait();
            $end = microtime(true);

            $response = new Response((string)$res->getBody(), $res->getStatusCode());

            if (defined('DEBUG') && DEBUG) {
                $response->setQueryTime($end - $start);
            }

            $response->setTransferInfo(
                array(
                    'request_header' => $request->getMethod(),
                    'http_code' => $res->getStatusCode()
                )
            );

            if ($response->hasError()) {
                throw new ResponseException($request, $response);
            }

            if ($response->hasFailedShards()) {
                throw new PartialShardFailureException($request, $response);
            }

            return $response;

        } catch (ClientException $e) {
            // ignore 4xx errors
        } catch (TransferException $e) {
            throw new GuzzleException($e, $request, new Response($e->getMessage()));
        }
    }

    /**
     * Return Guzzle resource
     *
     * @param  bool $persistent False if not persistent connection
     * @return resource Connection resource
     */
    protected function _getGuzzleClient($baseUrl, $persistent = true)
    {
        return self::$_guzzleClientConnection;
    }

    public function setGuzzleClient($client) {
        self::$_guzzleClientConnection = $client;
    }

    /**
     * Builds the base url for the guzzle connection
     *
     * @param  \Elastica\Connection $connection
     */
    protected function _getBaseUrl(Connection $connection)
    {
        // If url is set, url is taken. Otherwise port, host and path
        $url = $connection->hasConfig('url') ? $connection->getConfig('url') : '';

        if (!empty($url)) {
            $baseUri = $url;
        } else {
            $baseUri = $this->_scheme . '://' . $connection->getHost() . ':' . $connection->getPort() . '/' . $connection->getPath();
        }
        return rtrim($baseUri, '/');
    }

    /**
     * Builds the action path url for each request
     *
     * @param  \Elastica\Request $request
     */
    protected function _getActionPath(Request $request)
    {
        $action = $request->getPath();
        if ($action) {
            $action = '/'. ltrim($action, '/');
        }
        $query = $request->getQuery();

        if (!empty($query)) {
            $action .= '?' . http_build_query($query);
        }

        return $action;
    }
}
