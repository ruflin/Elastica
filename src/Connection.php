<?php

namespace Elastica;

use Elastica\Exception\InvalidException;
use Elastica\Transport\AbstractTransport;

/**
 * Elastica connection instance to an elasticasearch node.
 *
 * @author   Nicolas Ruflin <spam@ruflin.com>
 */
class Connection extends Param
{
    /**
     * Default elastic search port.
     */
    public const DEFAULT_PORT = 9200;

    /**
     * Default host.
     */
    public const DEFAULT_HOST = 'localhost';

    /**
     * Default transport.
     *
     * @var string
     */
    public const DEFAULT_TRANSPORT = 'Http';

    /**
     * Default compression.
     *
     * @var string
     */
    public const DEFAULT_COMPRESSION = false;

    /**
     * Number of seconds after a timeout occurs for every request
     * If using indexing of file large value necessary.
     */
    public const TIMEOUT = 300;

    /**
     * Number of seconds after a connection timeout occurs for every request during the connection phase.
     *
     * @see Connection::setConnectTimeout();
     */
    public const CONNECT_TIMEOUT = 0;

    /**
     * Creates a new connection object. A connection is enabled by default.
     *
     * @param array $params OPTIONAL Connection params: host, port, transport, timeout. All are optional
     */
    public function __construct(array $params = [])
    {
        $this->setParams($params);
        $this->setEnabled(true);

        // Set empty config param if not exists
        if (!$this->hasParam('config')) {
            $this->setParam('config', []);
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
     * @param int $port
     *
     * @return $this
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
     * @param string $host
     *
     * @return $this
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
     *
     * @param string|null $proxy
     *
     * @return $this
     */
    public function setProxy($proxy)
    {
        return $this->setParam('proxy', $proxy);
    }

    /**
     * @return array|string
     */
    public function getTransport()
    {
        return $this->hasParam('transport') ? $this->getParam('transport') : self::DEFAULT_TRANSPORT;
    }

    /**
     * @param array|string $transport
     *
     * @return $this
     */
    public function setTransport($transport)
    {
        return $this->setParam('transport', $transport);
    }

    /**
     * @return bool
     */
    public function hasCompression()
    {
        return (bool) $this->hasParam('compression') ? $this->getParam('compression') : self::DEFAULT_COMPRESSION;
    }

    /**
     * @param bool $compression
     *
     * @return $this
     */
    public function setCompression($compression = null)
    {
        return $this->setParam('compression', $compression);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->hasParam('path') ? $this->getParam('path') : '';
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        return $this->setParam('path', $path);
    }

    /**
     * @param int $timeout Timeout in seconds
     *
     * @return $this
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
     * Number of seconds after a connection timeout occurs for every request during the connection phase.
     * Use a small value if you need a fast fail in case of dead, unresponsive or unreachable servers (~5 sec).
     *
     * Set to zero to switch to the default built-in connection timeout (300 seconds in curl).
     *
     * @see http://curl.haxx.se/libcurl/c/CURLOPT_CONNECTTIMEOUT.html
     *
     * @param int $timeout Connect timeout in seconds
     *
     * @return $this
     */
    public function setConnectTimeout($timeout)
    {
        return $this->setParam('connectTimeout', $timeout);
    }

    /**
     * @return int Connection timeout in seconds
     */
    public function getConnectTimeout()
    {
        return (int) $this->hasParam('connectTimeout') ? $this->getParam('connectTimeout') : self::CONNECT_TIMEOUT;
    }

    /**
     * Enables a connection.
     *
     * @param bool $enabled OPTIONAL (default = true)
     *
     * @return $this
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
     * Returns an instance of the transport type.
     *
     * @throws InvalidException If invalid transport type
     *
     * @return AbstractTransport Transport object
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
     * @return $this
     */
    public function setConfig(array $config)
    {
        return $this->setParam('config', $config);
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function addConfig($key, $value)
    {
        $this->_params['config'][$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasConfig($key)
    {
        $config = $this->getConfig();

        return isset($config[$key]);
    }

    /**
     * Returns a specific config key or the whole
     * config array if not set.
     *
     * @param string $key Config key
     *
     * @throws InvalidException
     *
     * @return array|bool|int|string|null Config value
     */
    public function getConfig($key = '')
    {
        $config = $this->getParam('config');
        if (empty($key)) {
            return $config;
        }

        if (!\array_key_exists($key, $config)) {
            throw new InvalidException('Config key is not set: '.$key);
        }

        return $config[$key];
    }

    /**
     * @param array|Connection $params Params to create a connection
     *
     * @throws Exception\InvalidException
     *
     * @return self
     */
    public static function create($params = [])
    {
        if (\is_array($params)) {
            return new static($params);
        }

        if ($params instanceof self) {
            return $params;
        }

        throw new InvalidException('Invalid data type');
    }

    /**
     * @return string User
     */
    public function getUsername()
    {
        return $this->hasParam('username') ? $this->getParam('username') : null;
    }

    /**
     * @return string Password
     */
    public function getPassword()
    {
        return $this->hasParam('password') ? $this->getParam('password') : null;
    }

    /**
     * @return string AuthType
     */
    public function getAuthType()
    {
        return $this->hasParam('auth_type') ? \strtolower($this->getParam('auth_type')) : null;
    }
}
