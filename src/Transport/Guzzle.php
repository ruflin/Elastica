<?php

namespace Elastica\Transport;

use Elastica\Connection;
use Elastica\Exception\Connection\GuzzleException;
use Elastica\Exception\PartialShardFailureException;
use Elastica\Exception\ResponseException;
use Elastica\JSON;
use Elastica\Request;
use Elastica\Response;
use Elastica\Util;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;

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
     * @var Client|null Guzzle client to reuse
     */
    protected static $_guzzleClientConnection;

    /**
     * Makes calls to the elasticsearch server.
     *
     * All calls that are made to the server are done through this function
     *
     * @throws \Elastica\Exception\ConnectionException
     * @throws ResponseException
     * @throws \Elastica\Exception\Connection\HttpException
     */
    public function exec(Request $request, array $params): Response
    {
        $connection = $this->getConnection();

        $client = $this->_getGuzzleClient($connection->isPersistent());

        $options = [
            'base_uri' => $this->_getBaseUrl($connection),
            RequestOptions::HEADERS => [
                'Content-Type' => $request->getContentType(),
            ],
            RequestOptions::HTTP_ERRORS => false, // 4xx and 5xx is expected and NOT an exceptions in this context
        ];

        if ($connection->getTimeout()) {
            $options[RequestOptions::TIMEOUT] = $connection->getTimeout();
        }

        if (null !== $proxy = $connection->getProxy()) {
            $options[RequestOptions::PROXY] = $proxy;
        }

        $req = $this->_createPsr7Request($request, $connection);

        try {
            $start = \microtime(true);
            $res = $client->send($req, $options);
            $end = \microtime(true);
        } catch (TransferException $ex) {
            throw new GuzzleException($ex, $request, new Response($ex->getMessage()));
        }

        $responseBody = (string) $res->getBody();
        $response = new Response($responseBody, $res->getStatusCode());
        $response->setQueryTime($end - $start);
        if ($connection->hasConfig('bigintConversion')) {
            $response->setJsonBigintConversion($connection->getConfig('bigintConversion'));
        }

        $response->setTransferInfo(
            [
                'request_header' => $request->getMethod(),
                'http_code' => $res->getStatusCode(),
            ]
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
     * @return Psr7\Request
     */
    protected function _createPsr7Request(Request $request, Connection $connection)
    {
        $req = new Psr7\Request(
            $request->getMethod(),
            $this->_getActionPath($request),
            $connection->hasConfig('headers') && \is_array($connection->getConfig('headers'))
                ? $connection->getConfig('headers')
                : []
        );

        $data = $request->getData();
        if (!empty($data) || '0' === $data) {
            if (Request::GET === $req->getMethod()) {
                $req = $req->withMethod(Request::POST);
            }

            if ($this->hasParam('postWithRequestBody') && true == $this->getParam('postWithRequestBody')) {
                $request->setMethod(Request::POST);
                $req = $req->withMethod(Request::POST);
            }

            $req = $req->withBody(
                Psr7\stream_for(
                    \is_array($data)
                    ? JSON::stringify($data, \JSON_UNESCAPED_UNICODE)
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
     */
    protected function _getGuzzleClient(bool $persistent = true): Client
    {
        if (!$persistent || !self::$_guzzleClientConnection) {
            self::$_guzzleClientConnection = new Client();
        }

        return self::$_guzzleClientConnection;
    }

    /**
     * Builds the base url for the guzzle connection.
     */
    protected function _getBaseUrl(Connection $connection): string
    {
        // If url is set, url is taken. Otherwise port, host and path
        $url = $connection->hasConfig('url') ? $connection->getConfig('url') : '';

        if (!empty($url)) {
            $baseUri = $url;
        } else {
            $baseUri = (string) Uri::fromParts([
                'scheme' => $this->_scheme,
                'host' => $connection->getHost(),
                'port' => $connection->getPort(),
                'path' => \ltrim($connection->getPath(), '/'),
            ]);
        }

        return \rtrim($baseUri, '/');
    }

    /**
     * Builds the action path url for each request.
     */
    protected function _getActionPath(Request $request): string
    {
        $action = $request->getPath();
        if ($action) {
            $action = '/'.\ltrim($action, '/');
        }

        if (!Util::isDateMathEscaped($action)) {
            $action = Util::escapeDateMath($action);
        }

        $query = $request->getQuery();

        if (!empty($query)) {
            $action .= '?'.\http_build_query(
                $this->sanityzeQueryStringBool($query)
            );
        }

        return $action;
    }
}
