<?php

namespace Elastica\Transport;

use Elastica\Connection;
use Elastica\Request;
use Elastica\Exception\InvalidException;
use Elastica\Param;

/**
 * Elastica Abstract Transport object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
abstract class AbstractTransport extends Param
{
    /**
     * @var \Elastica\Connection
     */
    protected $_connection;

    /**
     * Construct transport
     *
     * @param \Elastica\Connection $connection Connection object
     */
    public function __construct(Connection $connection = null)
    {
        if ($connection) {
            $this->setConnection($connection);
        }
    }

    /**
     * @return \Elastica\Connection Connection object
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * @param \Elastica\Connection $connection Connection object
     */
    public function setConnection(Connection $connection)
    {
        $this->_connection = $connection;
    }

    /**
     * Executes the transport request
     *
     * @param  \Elastica\Request  $request Request object
     * @param  array             $params  Hostname, port, path, ...
     * @return \Elastica\Response Response object
     */
    abstract public function exec(Request $request, array $params);

    /**
     * Create a transport
     *
     * The $transport parameter can be one of the following values:
     *
     * * string: The short name of a transport. For instance "Http", "Memcache" or "Thrift"
     * * object: An already instantiated instance of a transport
     * * array: An array with a "type" key which must be set to one of the two options. All other
     *          keys in the array will be set as parameters in the transport instance
     *
     * @param mixed $transport A transport definition
     * @param \Elastica\Connection $connection A connection instance
     * @param array $params Parameters for the transport class
     * @throws \Elastica\Exception\InvalidException
     * @return AbstractTransport
     */
    public static function create($transport, Connection $connection, array $params = array())
    {
        if (is_array($transport) && isset($transport['type'])) {
            $transportParams = $transport;
            unset($transportParams['type']);

            $params = array_replace($params, $transportParams);
            $transport = $transport['type'];
        }

        if (is_string($transport)) {
            $className = 'Elastica\\Transport\\' . $transport;

            if (!class_exists($className)) {
                throw new InvalidException('Invalid transport');
            }

            $transport = new $className;
        }

        if ($transport instanceof AbstractTransport) {
            $transport->setConnection($connection);

            foreach ($params as $key => $value) {
                $transport->setParam($key, $value);
            }
        } else {
            throw new InvalidException('Invalid transport');
        }

        return $transport;
    }
}
