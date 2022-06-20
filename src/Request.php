<?php

namespace Elastica;

use Elastica\Exception\ConnectionException;
use Elastica\Exception\InvalidException;
use Elastica\Exception\ResponseException;

/**
 * Elastica Request object.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Request extends Param
{
    public const HEAD = 'HEAD';
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const GET = 'GET';
    public const DELETE = 'DELETE';
    public const DEFAULT_CONTENT_TYPE = 'application/json';
    public const NDJSON_CONTENT_TYPE = 'application/x-ndjson';

    /**
     * @var Connection|null
     */
    protected $_connection;

    /**
     * Construct.
     *
     * @param string       $path        Request path
     * @param string       $method      OPTIONAL Request method (use const's) (default = self::GET)
     * @param array|string $data        OPTIONAL Data array
     * @param array        $query       OPTIONAL Query params
     * @param string       $contentType Content-Type sent with this request
     */
    public function __construct(string $path, string $method = self::GET, $data = [], array $query = [], ?Connection $connection = null, string $contentType = self::DEFAULT_CONTENT_TYPE)
    {
        $this->setPath($path);
        $this->setMethod($method);
        $this->setData($data);
        $this->setQuery($query);

        if ($connection) {
            $this->setConnection($connection);
        }
        $this->setContentType($contentType);
    }

    public function __toString(): string
    {
        return JSON::stringify($this->toArray());
    }

    /**
     * Sets the request method. Use one of the for consts.
     *
     * @return $this
     */
    public function setMethod(string $method)
    {
        return $this->setParam('method', $method);
    }

    /**
     * Get request method.
     */
    public function getMethod(): string
    {
        return $this->getParam('method');
    }

    /**
     * Sets the request data.
     *
     * @param array|string $data Request data
     *
     * @return $this
     */
    public function setData($data)
    {
        return $this->setParam('data', $data);
    }

    /**
     * Return request data.
     *
     * @return array|string Request data
     */
    public function getData()
    {
        return $this->getParam('data');
    }

    /**
     * Sets the request path.
     *
     * @return $this
     */
    public function setPath(string $path)
    {
        return $this->setParam('path', $path);
    }

    /**
     * Return request path.
     */
    public function getPath(): string
    {
        return $this->getParam('path');
    }

    /**
     * Return query params.
     *
     * @return array Query params
     */
    public function getQuery(): array
    {
        return $this->getParam('query');
    }

    /**
     * @return $this
     */
    public function setQuery(array $query = [])
    {
        return $this->setParam('query', $query);
    }

    /**
     * @return $this
     */
    public function setConnection(Connection $connection)
    {
        $this->_connection = $connection;

        return $this;
    }

    /**
     * Return Connection Object.
     *
     * @throws Exception\InvalidException If no valid connection was set
     */
    public function getConnection(): Connection
    {
        if (null === $this->_connection) {
            throw new InvalidException('No valid connection object set');
        }

        return $this->_connection;
    }

    /**
     * Set the Content-Type of this request.
     */
    public function setContentType(string $contentType)
    {
        return $this->setParam('contentType', $contentType);
    }

    /**
     * Get the Content-Type of this request.
     */
    public function getContentType(): string
    {
        return $this->getParam('contentType');
    }

    /**
     * Sends request to server.
     *
     * @throws ResponseException
     * @throws ConnectionException
     */
    public function send(): Response
    {
        $transport = $this->getConnection()->getTransportObject();

        // Refactor: Not full toArray needed in exec?
        return $transport->exec($this, $this->getConnection()->toArray());
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = $this->getParams();
        if ($this->_connection) {
            $data['connection'] = $this->_connection->getParams();
        }

        return $data;
    }

    /**
     * Converts request to curl request format.
     *
     * @deprecated since version 7.1.3, use the "__toString()" method or cast to string instead.
     *
     * @return string
     */
    public function toString()
    {
        \trigger_deprecation('ruflin/elastica', '7.1.3', 'The "%s()" method is deprecated, use "__toString()" or cast to string instead. It will be removed in 8.0.', __METHOD__);

        return (string) $this;
    }
}
