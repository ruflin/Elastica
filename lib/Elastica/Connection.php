<?php

namespace Elastica;

use Elastica\Exception\InvalidException;
use Elastica\Transport\AbstractTransport;

/**
 * Elastica connection instance to an elasticasearch node
 *
 * @category Xodoa
 * @package  Elastica
 * @author   Nicolas Ruflin <spam@ruflin.com>
 */
class Connection extends Param
{
    /**
     * Default elastic search port
     */
    const DEFAULT_PORT = 9200;

    /**
     * Default host
     */
    const DEFAULT_HOST = 'localhost';

    /**
     * Default transport
     *
     * @var string
     */
    const DEFAULT_TRANSPORT = 'Http';

    /**
     * Number of seconds after a timeout occurs for every request
     * If using indexing of file large value necessary.
     */
    const TIMEOUT = 300;

    /**
     * Creates a new connection object. A connection is enabled by default
     *
     * @param array $params OPTIONAL Connection params: host, port, transport, timeout. All are optional
     */
    public function __construct(array $params = array())
    {
        $this->setParams($params);
        $this->setEnabled(true);

        // Set empty config param if not exists
        if (!$this->hasParam('config')) {
            $this->setParam('config', array());
        }
    }

    /**
     * @return int Server port
     */
    public function getPort()
    {
        return $this->hasParam('port') ? $this->getParam('port') : self::DEFAULT_PORT;
    }

    /**
     * @param  int                  $port
     * @return \Elastica\Connection
     */
    public function setPort($port)
    {
        return $this->setParam('port', (int) $port);
    }

    /**
     * @return string Host
     */
    public function getHost()
    {
        return $this->hasParam('host') ? $this->getParam('host') : self::DEFAULT_HOST;
    }

    /**
     * @param  string               $host
     * @return \Elastica\Connection
     */
    public function setHost($host)
    {
        return $this->setParam('host', $host);
    }

    /**
     * @return string|null Host
     */
    public function getProxy()
    {
        return $this->hasParam('proxy') ? $this->getParam('proxy') : null;
    }

    /**
     * Set proxy for http connections. Null is for environmental proxy,
     * empty string to disable proxy and proxy string to set actual http proxy.
     *
     * @see http://curl.haxx.se/libcurl/c/curl_easy_setopt.html#CURLOPTPROXY
     * @param  string|null          $proxy
     * @return \Elastica\Connection
     */
    public function setProxy($proxy)
    {
        return $this->setParam('proxy', $proxy);
    }

    /**
     * @return string|array
     */
    public function getTransport()
    {
        return $this->hasParam('transport') ? $this->getParam('transport') : self::DEFAULT_TRANSPORT;
    }

    /**
     * @param  string|array         $transport
     * @return \Elastica\Connection
     */
    public function setTransport($transport)
    {
        return $this->setParam('transport', $transport);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->hasParam('path') ? $this->getParam('path') : '';
    }

    /**
     * @param  string               $path
     * @return \Elastica\Connection
     */
    public function setPath($path)
    {
        return $this->setParam('path', $path);
    }

    /**
     * @param  int                  $timeout Timeout in seconds
     * @return \Elastica\Connection
     */
    public function setTimeout($timeout)
    {
        return $this->setParam('timeout', $timeout);
    }

    /**
     * @return int Connection timeout in seconds
     */
    public function getTimeout()
    {
        return (int) $this->hasParam('timeout') ? $this->getParam('timeout') : self::TIMEOUT;
    }

    /**
     * Enables a connection
     *
     * @param  bool                 $enabled OPTIONAL (default = true)
     * @return \Elastica\Connection
     */
    public function setEnabled($enabled = true)
    {
        return $this->setParam('enabled', $enabled);
    }

    /**
     * @return bool True if enabled
     */
    public function isEnabled()
    {
        return (bool) $this->getParam('enabled');
    }

    /**
     * Returns an instance of the transport type
     *
     * @return \Elastica\Transport\AbstractTransport Transport object
     * @throws \Elastica\Exception\InvalidException  If invalid transport type
     */
    public function getTransportObject()
    {
        $transport = $this->getTransport();

        return AbstractTransport::create($transport, $this);
    }

    /**
     * @return bool Returns true if connection is persistent. True by default
     */
    public function isPersistent()
    {
        return (bool) $this->hasParam('persistent') ? $this->getParam('persistent') : true;
    }

    /**
     * @param  array                $config
     * @return \Elastica\Connection
     */
    public function setConfig(array $config)
    {
        return $this->setParam('config', $config);
    }

    /**
     * @param  string               $key
     * @param  mixed                $value
     * @return \Elastica\Connection
     */
    public function addConfig($key, $value)
    {
        $this->_params['config'][$key] = $value;

        return $this;
    }

    /**
     * @param  string $key
     * @return bool
     */
    public function hasConfig($key)
    {
        $config = $this->getConfig();

        return isset($config[$key]);
    }

    /**
     * Returns a specific config key or the whole
     * config array if not set
     *
     * @param  string                               $key Config key
     * @throws \Elastica\Exception\InvalidException
     * @return array|string                         Config value
     */
    public function getConfig($key = '')
    {
        $config = $this->getParam('config');
        if (empty($key)) {
            return $config;
        }

        if (!array_key_exists($key, $config)) {
            throw new InvalidException('Config key is not set: '.$key);
        }

        return $config[$key];
    }

    /**
     * @param  \Elastica\Connection|array $params Params to create a connection
     * @throws Exception\InvalidException
     * @return \Elastica\Connection
     */
    public static function create($params = array())
    {
        $connection = null;

        if ($params instanceof Connection) {
            $connection = $params;
        } elseif (is_array($params)) {
            $connection = new Connection($params);
        } else {
            throw new InvalidException('Invalid data type');
        }

        return $connection;
    }
}
