<?php
/**
 * Elastica Thrift Transport object
 *
 * @category Xodoa
 * @package Elastica
 * @author Mikhail Shamin <munk13@gmail.com>
 */

if (!isset($GLOBALS['THRIFT_ROOT'])) {
    $GLOBALS['THRIFT_ROOT'] = realpath(dirname(__FILE__) . '/../../../thrift');
}

include_once $GLOBALS['THRIFT_ROOT'].'/Thrift.php';
include_once $GLOBALS['THRIFT_ROOT'].'/transport/TSocket.php';
include_once $GLOBALS['THRIFT_ROOT'].'/protocol/TBinaryProtocol.php';
include_once $GLOBALS['THRIFT_ROOT'].'/packages/elasticsearch/Rest.php';

class Elastica_Transport_Thrift extends Elastica_Transport_Abstract
{
    /**
     * @var RestClient[]
     */
    protected $_clients = array();

    /**
     * @param string $host
     * @param int $port
     * @param int $sendTimeout millisecs
     * @param int $recvTimeout millisecs
     * @param bool $framedTransport
     * @return RestClient
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
            $transport = new TBufferedTransport($socket, 1024, 1024);
        }
        $protocol = new TBinaryProtocolAccelerated($transport);

        $client = new RestClient($protocol);

        $transport->open();

        return $client;
    }

    /**
     * @param string $host
     * @param int $port
     * @param int $sendTimeout
     * @param int $recvTimeout
     * @return RestClient
     */
    protected function _getClient($host, $port, $sendTimeout = null, $recvTimeout = null, $framedProtocol = false)
    {
        $key = $host . ':' . $port;
        if (!isset($this->_clients[$key])) {
            $this->_clients[$key] = $this->_createClient($host, $port, $sendTimeout, $recvTimeout, $framedProtocol);
        }
        return $this->_clients[$key];
    }

    /**
     * Makes calls to the elasticsearch server
     *
     * @param  array             $params Host, Port, ...
     * @return Elastica_Response Response object
     */
    public function exec(array $params)
    {
        $request = $this->getRequest();

        $sendTimeout = (!empty($params['sendTimeout'])) ? $params['sendTimeout'] : null;
        $recvTimeout = (!empty($params['recvTimeout'])) ? $params['recvTimeout'] : null;
        $framedProtocol = (!empty($params['framedProtocol'])) ? (bool) $params['framedProtocol'] : false;
        $client = $this->_getClient($params['host'], $params['port'], $sendTimeout, $recvTimeout, $framedProtocol);

        $restRequest = new Elasticsearch_RestRequest();
        $restRequest->method = $GLOBALS['Elasticsearch_E_Method'][$request->getMethod()];
        $restRequest->uri = $request->getPath();
        $query = $request->getQuery();
        if (!empty($query)) {
            $restRequest->parameters = $query;
        }

        $data = $request->getData();
        if (!empty($data)) {
            if (is_array($data)) {
                $content = json_encode($data);
            } else {
                $content = $data;
            }
            $restRequest->body = $content;
        }

        $start = microtime(true);
        /* @var $result Elasticsearch_RestResponse */
        try {
            $result = $client->execute($restRequest);
            $response = new Elastica_Response($result->body);
        } catch (TException $e) {
            $response = new Elastica_Response('');
            throw new Elastica_Exception_Transport($e, $request, $response);
        }

        $end = microtime(true);

        if (defined('DEBUG') && DEBUG) {
            $response->setQueryTime($end - $start);
        }

        if ($response->hasError()) {
            throw new Elastica_Exception_Response($response);
        }

        return $response;
    }
}
