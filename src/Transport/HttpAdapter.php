<?php

namespace Elastica\Transport;

use Elastica\Connection;
use Elastica\Exception\PartialShardFailureException;
use Elastica\Exception\ResponseException;
use Elastica\JSON;
use Elastica\Request as ElasticaRequest;
use Elastica\Response;
use Elastica\Response as ElasticaResponse;
use Elastica\Util;
use Ivory\HttpAdapter\HttpAdapterInterface;
use Ivory\HttpAdapter\Message\Request as HttpAdapterRequest;
use Ivory\HttpAdapter\Message\Response as HttpAdapterResponse;
use Ivory\HttpAdapter\Message\Stream\StringStream;

trigger_deprecation('ruflin/elastica', '7.1.1', 'The "%s" class is deprecated. It will be removed in 8.0.', HttpAdapter::class);

/**
 * @deprecated since version 7.1.1
 */
class HttpAdapter extends AbstractTransport
{
    /**
     * @var HttpAdapterInterface
     */
    private $httpAdapter;

    /**
     * @var string
     */
    private $_scheme = 'http';

    /**
     * Construct transport.
     */
    public function __construct(?Connection $connection, HttpAdapterInterface $httpAdapter)
    {
        parent::__construct($connection);
        $this->httpAdapter = $httpAdapter;
    }

    /**
     * Makes calls to the elasticsearch server.
     *
     * All calls that are made to the server are done through this function
     *
     * @param array $params Host, Port, ...
     *
     * @throws \Elastica\Exception\ConnectionException
     * @throws ResponseException
     * @throws \Elastica\Exception\Connection\HttpException
     */
    public function exec(ElasticaRequest $elasticaRequest, array $params): Response
    {
        $connection = $this->getConnection();

        if ($timeout = $connection->getTimeout()) {
            $this->httpAdapter->getConfiguration()->setTimeout($timeout);
        }

        $httpAdapterRequest = $this->_createHttpAdapterRequest($elasticaRequest, $connection);

        $start = \microtime(true);
        $httpAdapterResponse = $this->httpAdapter->sendRequest($httpAdapterRequest);
        $end = \microtime(true);

        $elasticaResponse = $this->_createElasticaResponse($httpAdapterResponse);
        $elasticaResponse->setQueryTime($end - $start);

        $elasticaResponse->setTransferInfo(
            [
                'request_header' => $httpAdapterRequest->getMethod(),
                'http_code' => $httpAdapterResponse->getStatusCode(),
            ]
        );

        if ($elasticaResponse->hasError()) {
            throw new ResponseException($elasticaRequest, $elasticaResponse);
        }

        if ($elasticaResponse->hasFailedShards()) {
            throw new PartialShardFailureException($elasticaRequest, $elasticaResponse);
        }

        return $elasticaResponse;
    }

    protected function _createElasticaResponse(HttpAdapterResponse $httpAdapterResponse): ElasticaResponse
    {
        return new ElasticaResponse((string) $httpAdapterResponse->getBody(), $httpAdapterResponse->getStatusCode());
    }

    protected function _createHttpAdapterRequest(ElasticaRequest $elasticaRequest, Connection $connection): HttpAdapterRequest
    {
        $data = $elasticaRequest->getData();
        $body = null;
        $method = $elasticaRequest->getMethod();
        $headers = $connection->hasConfig('headers') ?: [];
        if (!empty($data) || '0' === $data) {
            if (ElasticaRequest::GET === $method) {
                $method = ElasticaRequest::POST;
            }

            if ($this->hasParam('postWithRequestBody') && true == $this->getParam('postWithRequestBody')) {
                $elasticaRequest->setMethod(ElasticaRequest::POST);
                $method = ElasticaRequest::POST;
            }

            if (\is_array($data)) {
                $body = JSON::stringify($data, \JSON_UNESCAPED_UNICODE);
            } else {
                $body = $data;
            }
        }

        $url = $this->_getUri($elasticaRequest, $connection);
        $streamBody = new StringStream($body);

        return new HttpAdapterRequest($url, $method, HttpAdapterRequest::PROTOCOL_VERSION_1_1, $headers, $streamBody);
    }

    protected function _getUri(ElasticaRequest $request, Connection $connection): string
    {
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
            $baseUri .= '?'.\http_build_query($query);
        }

        return $baseUri;
    }
}
