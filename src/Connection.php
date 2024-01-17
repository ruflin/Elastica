<?php

namespace Elastica;

use Elastic\Transport\Transport;
use Elastica\Exception\InvalidException;

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
     * @var bool
     */
    public const DEFAULT_COMPRESSION = false;

    /**
     * Creates a new connection object. A connection is enabled by default.
     *
     * @param array $params OPTIONAL Connection params: host, port. All are optional
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

    public function getTransport(): Transport
    {
        return $this->getParam('transport');
    }

    /**
     * @return $this
     */
    public function setTransport(Transport $transport)
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
     * @return Transport Transport object
     */
    public function getTransportObject(): Transport
    {
        return $this->getTransport();
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
     * @return string|null User
     */
    public function getUsername()
    {
        return $this->hasParam('username') ? $this->getParam('username') : null;
    }

    /**
     * @return string|null Password
     */
    public function getPassword()
    {
        return $this->hasParam('password') ? $this->getParam('password') : null;
    }
}
