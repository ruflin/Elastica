<?php
/**
 * Elastica Abstract Transport object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
abstract class Elastica_Transport_Abstract
{
    /**
     * Path
     *
     * @var string path
     */
    protected $_path;

    /**
     * Method
     *
     * @var string method
     * @todo set default method?
     */
    protected $_method;

    /**
     * Data
     *
     * @var array Data
     */
    protected $_data;

    /**
     * Config
     *
     * @var array config
     */
    protected $_config;

	/**
	 * @var Elastica_Connection
	 */
	protected $_connection;

    /**
     * Construct transport
     *
     * @param Elastica_Connection $connection Connection object
     */
    public function __construct(Elastica_Connection $connection)
    {
        $this->_connection = $connection;
    }

    /**
     * @return Elastica_Connection Connection object
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * Executes the transport request
     *
	 * @param Elastica_Request	$request Request object
     * @param  array             $params Hostname, port, path, ...
     * @return Elastica_Response Response object
     */
    abstract public function exec(Elastica_Request $request, array $params);
}
