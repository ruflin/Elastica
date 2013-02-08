<?php

namespace Elastica\Transport;

use Elastica\Connection;
use Elastica\Request;

/**
 * Elastica Abstract Transport object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
abstract class AbstractTransport
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
     * @var \Elastica\Connection
     */
    protected $_connection;

    /**
     * Construct transport
     *
     * @param \Elastica\Connection $connection Connection object
     */
    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }

    /**
     * @return \Elastica\Connection Connection object
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * Executes the transport request
     *
     * @param  \Elastica\Request  $request Request object
     * @param  array             $params  Hostname, port, path, ...
     * @return \Elastica\Response Response object
     */
    abstract public function exec(Request $request, array $params);
}
