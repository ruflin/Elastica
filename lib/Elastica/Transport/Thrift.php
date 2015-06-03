<?php
namespace Elastica\Transport;

use Elastica\Connection;
use Elastica\Exception\Connection\ThriftException;
use Elastica\Exception\PartialShardFailureException;
use Elastica\Exception\ResponseException;
use Elastica\Exception\RuntimeException;
use Elastica\JSON;
use Elastica\Request;
use Elastica\Response;
use Elasticsearch\Method;
use Elasticsearch\RestClient;
use Elasticsearch\RestRequest;
use Elasticsearch\RestResponse;
use Thrift\Exception\TException;
use Thrift\Protocol\TBinaryProtocolAccelerated;
use Thrift\Transport\TBufferedTransport;
use Thrift\Transport\TFramedTransport;
use Thrift\Transport\TSocket;

/**
 * Elastica Thrift Transport object.
 *
 * @author Mikhail Shamin <munk13@gmail.com>
 *
 * @deprecated The thrift transport is deprecated as of ES 1.5, and will be removed in ES 2.0
 */
class Thrift extends AbstractTransport
{
    /**
     * @var RestClient[]
     */
    protected $_clients = array();

    /**
     * Construct transport.
     *
     * @param \Elastica\Connection $connection Connection object
     *
     * @throws \Elastica\Exception\RuntimeException
     */
    public function __construct(Connection $connection = null)
    {
        parent::__construct($connection);
        if (!class_exists('Elasticsearch\\RestClient')) {
            throw new RuntimeException('Elasticsearch\\RestClient class not found. Check that suggested package munkie/elasticsearch-thrift-php is required in composer.json');
        }
    }

    /**
     * @param string $host
     * @param int    $port
     * @param int    $sendTimeout     msec
     * @param int    $recvTimeout     msec
     * @param bool   $framedTransport
     *
     * @return \Elasticsearch\RestClient
     */
    protected function _createClient($host, $port, $sendTimeout = null, $recvTimeout = null, $framedTransport = false)
    {
        $socket = new TSocket($host, $port, true);

        if (null !== $sendTimeout) {
            $socket->setSendTimeout($sendTimeout);
        }

        if (null !== $recvTimeout) {
            $socket->setRecvTimeout($recvTimeout);
        }

        if ($framedTransport) {
            $transport = new TFramedTransport($socket);
        } else {
            $transport = new TBufferedTransport($socket);
        }
        $protocol = new TBinaryProtocolAccelerated($transport);

        $client = new RestClient($protocol);

        $transport->open();

        return $client;
    }

    /**
     * @param string $host
     * @param int    $port
     * @param int    $sendTimeout
     * @param int    $recvTimeout
     * @param bool   $framedTransport
     *
     * @return \Elasticsearch\RestClient
     */
    protected function _getClient($host, $port, $sendTimeout = null, $recvTimeout = null, $framedTransport = false)
    {
        $key = $host.':'.$port;
        if (!isset($this->_clients[$key])) {
            $this->_clients[$key] = $this->_createClient($host, $port, $sendTimeout, $recvTimeout, $framedTransport);
        }

        return $this->_clients[$key];
    }

    /**
     * Makes calls to the elasticsearch server.
     *
     * @param \Elastica\Request $request
     * @param array             $params  Host, Port, ...
     *
     * @throws \Elastica\Exception\Connection\ThriftException
     * @throws \Elastica\Exception\ResponseException
     *
     * @return \Elastica\Response Response object
     */
    public function exec(Request $request, array $params)
    {
        $connection = $this->getConnection();

        $sendTimeout = $connection->hasConfig('sendTimeout') ? $connection->getConfig('sendTimeout') : null;
        $recvTimeout = $connection->hasConfig('recvTimeout') ? $connection->getConfig('recvTimeout') : null;
        $framedTransport = $connection->hasConfig('framedTransport') ? (bool) $connection->getConfig('framedTransport') : false;

        try {
            $client = $this->_getClient(
                $connection->getHost(),
                $connection->getPort(),
                $sendTimeout,
                $recvTimeout,
                $framedTransport
            );

            $restRequest = new RestRequest();
            $restRequest->method = array_search($request->getMethod(), Method::$__names);
            $restRequest->uri = $request->getPath();

            $query = $request->getQuery();
            if (!empty($query)) {
                $restRequest->parameters = $query;
            }

            $data = $request->getData();
            if (!empty($data) || '0' === $data) {
                if (is_array($data)) {
                    $content = JSON::stringify($data);
                } else {
                    $content = $data;
                }
                $restRequest->body = $content;
            }

            /* @var $result RestResponse */
            $start = microtime(true);

            $result = $client->execute($restRequest);
            $response = new Response($result->body);

            $end = microtime(true);
        } catch (TException $e) {
            $response = new Response('');
            throw new ThriftException($e, $request, $response);
        }

        $response->setQueryTime($end - $start);

        if ($response->hasError()) {
            throw new ResponseException($request, $response);
        }

        if ($response->hasFailedShards()) {
            throw new PartialShardFailureException($request, $response);
        }

        return $response;
    }
}
