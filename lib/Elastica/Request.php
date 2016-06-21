<?php
namespace Elastica;

use Elastica\Exception\InvalidException;

/**
 * Elastica Request object.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Request extends Param
{
    const HEAD = 'HEAD';
    const POST = 'POST';
    const PUT = 'PUT';
    const GET = 'GET';
    const DELETE = 'DELETE';

    /**
     * @var \Elastica\Connection
     */
    protected $_connection;

    /**
     * Construct.
     *
     * @param string     $path       Request path
     * @param string     $method     OPTIONAL Request method (use const's) (default = self::GET)
     * @param array      $data       OPTIONAL Data array
     * @param array      $query      OPTIONAL Query params
     * @param Connection $connection
     *
     * @return \Elastica\Request OPTIONAL Connection object
     */
    public function __construct($path, $method = self::GET, $data = [], array $query = [], Connection $connection = null)
    {
        $this->setPath($path);
        $this->setMethod($method);
        $this->setData($data);
        $this->setQuery($query);

        if ($connection) {
            $this->setConnection($connection);
        }
    }

    /**
     * Sets the request method. Use one of the for consts.
     *
     * @param string $method Request method
     *
     * @return $this
     */
    public function setMethod($method)
    {
        return $this->setParam('method', $method);
    }

    /**
     * Get request method.
     *
     * @return string Request method
     */
    public function getMethod()
    {
        return $this->getParam('method');
    }

    /**
     * Sets the request data.
     *
     * @param array $data Request data
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
     * @return array Request data
     */
    public function getData()
    {
        return $this->getParam('data');
    }

    /**
     * Sets the request path.
     *
     * @param string $path Request path
     *
     * @return $this
     */
    public function setPath($path)
    {
        return $this->setParam('path', $path);
    }

    /**
     * Return request path.
     *
     * @return string Request path
     */
    public function getPath()
    {
        return $this->getParam('path');
    }

    /**
     * Return query params.
     *
     * @return array Query params
     */
    public function getQuery()
    {
        return $this->getParam('query');
    }

    /**
     * @param array $query
     *
     * @return $this
     */
    public function setQuery(array $query = [])
    {
        return $this->setParam('query', $query);
    }

    /**
     * @param \Elastica\Connection $connection
     *
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
     * @throws Exception\InvalidException If no valid connection was setted
     *
     * @return \Elastica\Connection
     */
    public function getConnection()
    {
        if (empty($this->_connection)) {
            throw new InvalidException('No valid connection object set');
        }

        return $this->_connection;
    }

    /**
     * Sends request to server.
     *
     * @return \Elastica\Response Response object
     */
    public function send()
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
     * @return string
     */
    public function toString()
    {
        return JSON::stringify($this->toArray());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
