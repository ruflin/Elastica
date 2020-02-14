<?php

namespace Elastica\Transport;

use Elastica\Connection;
use Elastica\Exception\InvalidException;
use Elastica\Param;
use Elastica\Request;
use Elastica\Response;

/**
 * Elastica Abstract Transport object.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
abstract class AbstractTransport extends Param
{
    /**
     * @var Connection
     */
    protected $_connection;

    /**
     * Construct transport.
     */
    public function __construct(?Connection $connection = null)
    {
        if ($connection) {
            $this->setConnection($connection);
        }
    }

    public function getConnection(): Connection
    {
        return $this->_connection;
    }

    /**
     * @return $this
     */
    public function setConnection(Connection $connection): AbstractTransport
    {
        $this->_connection = $connection;

        return $this;
    }

    /**
     * Executes the transport request.
     *
     * @param Request $request Request object
     * @param array   $params  Hostname, port, path, ...
     */
    abstract public function exec(Request $request, array $params): Response;

    /**
     * BOOL values true|false should be sanityzed and passed to Elasticsearch
     * as string.
     *
     * @return mixed
     */
    public function sanityzeQueryStringBool(array $query)
    {
        foreach ($query as $key => $value) {
            if (\is_bool($value)) {
                $query[$key] = ($value) ? 'true' : 'false';
            }
        }

        return $query;
    }

    /**
     * Create a transport.
     *
     * The $transport parameter can be one of the following values:
     *
     * * string: The short name of a transport. For instance "Http"
     * * object: An already instantiated instance of a transport
     * * array: An array with a "type" key which must be set to one of the two options. All other
     *          keys in the array will be set as parameters in the transport instance
     *
     * @param mixed      $transport  A transport definition
     * @param Connection $connection A connection instance
     * @param array      $params     Parameters for the transport class
     *
     * @throws InvalidException
     */
    public static function create($transport, Connection $connection, array $params = []): AbstractTransport
    {
        if (\is_array($transport) && isset($transport['type'])) {
            $transportParams = $transport;
            unset($transportParams['type']);

            $params = \array_replace($params, $transportParams);
            $transport = $transport['type'];
        }

        if (\is_string($transport)) {
            $specialTransports = [
                'httpadapter' => 'HttpAdapter',
                'nulltransport' => 'NullTransport',
            ];

            if (isset($specialTransports[\strtolower($transport)])) {
                $transport = $specialTransports[\strtolower($transport)];
            } else {
                $transport = \ucfirst($transport);
            }
            $classNames = ["Elastica\\Transport\\{$transport}", $transport];
            foreach ($classNames as $className) {
                if (\class_exists($className)) {
                    $transport = new $className();
                    break;
                }
            }
        }

        if ($transport instanceof self) {
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
