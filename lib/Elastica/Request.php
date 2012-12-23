<?php
/**
 * Elastica Request object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Request
{
    const POST = 'POST';
    const PUT = 'PUT';
    const GET = 'GET';
    const DELETE = 'DELETE';

    /**
     * Client
     *
     * @var Elastica_Client Client object
     */
    protected $_client;

    /**
     * Request path
     *
     * @var string Request path
     */
    protected $_path;

    /**
     * Request method (use const's)
     *
     * @var string Request method (use const's)
     */
    protected $_method;

    /**
     * Data array
     *
     * @var array Data array
     */
    protected $_data;

    /**
     * Query params
     *
     * @var array Query params
     */
    protected $_query;

	/**
	 * @var Elastica_Connection
	 */
	protected $_connection;

    /**
     * Internal id of last used server. This is used for round robin
     *
     * @var int Last server id
     */
    protected static $_serverId = null;

    /**
     * Construct
     *
     * @param Elastica_Connection $connection
     * @param string          $path   Request path
     * @param string          $method Request method (use const's)
     * @param array           $data   OPTIONAL Data array
     * @param array           $query  OPTIONAL Query params
     */
    public function __construct(Elastica_Connection $connection, $path, $method, $data = array(), array $query = array())
    {
        $this->_connection = $connection;
        $this->_path = $path;
        $this->_method = $method;
        $this->_data = $data;
        $this->_query = $query;
    }

    /**
     * Sets the request method. Use one of the for consts
     *
     * @param  string           $method Request method
     * @return Elastica_Request Current object
     */
    public function setMethod($method)
    {
        $this->_method = $method;

        return $this;
    }

    /**
     * Get request method
     *
     * @return string Request method
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * Sets the request data
     *
     * @param array $data Request data
     * @return Elastica_Request
     */
    public function setData($data)
    {
        $this->_data = $data;

        return $this;
    }

    /**
     * Return request data
     *
     * @return array Request data
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Sets the request path
     *
     * @param  string           $path Request path
     * @return Elastica_Request Current object
     */
    public function setPath($path)
    {
        $this->_path = $path;

        return $this;
    }

    /**
     * Return request path
     *
     * @return string Request path
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Return query params
     *
     * @return array Query params
     */
    public function getQuery()
    {
        return $this->_query;
    }

    /**
     * Return Connection Object
     *
     * @return Elastica_Connection
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * Sends request to server
     *
     * @return Elastica_Response Response object
     */
    public function send()
    {
		$transport = $this->getConnection()->getTransportObject();
		return $transport->exec($this, $this->getConnection()->toArray());
    }
}
