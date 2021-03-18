<?php

namespace Elastica;

use Elastica\Exception\InvalidException;
use Nyholm\Dsn\Configuration\Url;
use Nyholm\Dsn\DsnParser;
use Nyholm\Dsn\Exception\ExceptionInterface as DsnException;
use Nyholm\Dsn\Exception\FunctionNotSupportedException;

/**
 * Elastica client configuration.
 *
 * @author Antoine Lamirault <lamiraultantoine@gmail.com>
 */
class ClientConfiguration
{
    /**
     * Config with defaults.
     *
     * retryOnConflict: Use in \Elastica\Client::updateDocument
     * bigintConversion: Set to true to enable the JSON bigint to string conversion option (see issue #717)
     *
     * @var array
     */
    protected $configuration = [
        'host' => null,
        'port' => null,
        'path' => null,
        'url' => null,
        'proxy' => null,
        'transport' => null,
        'persistent' => true,
        'timeout' => null,
        'connections' => [], // host, port, path, transport, compression, persistent, timeout, username, password, auth_type, config -> (curl, headers, url)
        'roundRobin' => false,
        'retryOnConflict' => 0,
        'bigintConversion' => false,
        'username' => null,
        'password' => null,
        'auth_type' => null, //basic, digest, gssnegotiate, ntlm
    ];

    /**
     * Create configuration.
     *
     * @param array $config Additional config
     *
     * @return ClientConfiguration
     */
    public static function fromArray(array $config): self
    {
        $clientConfiguration = new static();
        foreach ($config as $key => $value) {
            $clientConfiguration->set($key, $value);
        }

        return $clientConfiguration;
    }

    /**
     * Create configuration from Dsn string. Example of valid DSN strings:
     * - http://localhost
     * - http://foo:bar@localhost:1234?timeout=4&persistant=false
     * - pool(http://127.0.0.1 http://127.0.0.2/bar?timeout=4).
     *
     * @return ClientConfiguration
     */
    public static function fromDsn(string $dsnString): self
    {
        try {
            $func = DsnParser::parseFunc($dsnString);
        } catch (DsnException $e) {
            throw new InvalidException(\sprintf('DSN "%s" is invalid.', $dsnString), 0, $e);
        }

        if ('dsn' === $func->getName()) {
            /** @var Url $dsn */
            $dsn = $func->first();
            $clientConfiguration = self::fromArray(self::parseDsn($dsn));
        } elseif ('pool' === $func->getName()) {
            $connections = [];
            $clientConfiguration = new static();
            foreach ($func->getArguments() as $arg) {
                $connections[] = self::parseDsn($arg);
            }
            $clientConfiguration->set('connections', $connections);
        } else {
            throw new FunctionNotSupportedException($dsnString, $func->getName());
        }

        foreach ($func->getParameters() as $optionName => $optionValue) {
            if ('false' === $optionValue) {
                $optionValue = false;
            } elseif ('true' === $optionValue) {
                $optionValue = true;
            } elseif (\is_numeric($optionValue)) {
                $optionValue = (int) $optionValue;
            }

            $clientConfiguration->set($optionName, $optionValue);
        }

        return $clientConfiguration;
    }

    /**
     * Returns a specific config key or the whole config array if not set.
     *
     * @throws InvalidException if the given key is not found in the configuration
     *
     * @return mixed Config value
     */
    public function get(string $key)
    {
        if (empty($key)) {
            return $this->configuration;
        }

        if (!$this->has($key)) {
            throw new InvalidException('Config key is not set: '.$key);
        }

        return $this->configuration[$key];
    }

    /**
     * Returns boolean indicates if configuration has key.
     */
    public function has(string $key): bool
    {
        return \array_key_exists($key, $this->configuration);
    }

    /**
     * Return all configuration.
     */
    public function getAll(): array
    {
        return $this->configuration;
    }

    /**
     * @param string $key   Key to set
     * @param mixed  $value Value
     */
    public function set(string $key, $value): void
    {
        $this->configuration[$key] = $value;
    }

    /**
     * Add value to a key. If original value is not an array, value is wrapped.
     *
     * @param string $key   Key to add
     * @param mixed  $value Value
     */
    public function add(string $key, $value): void
    {
        if (!\array_key_exists($key, $this->configuration)) {
            $this->configuration[$key] = [$value];
        } else {
            if (\is_array($this->configuration[$key])) {
                $this->configuration[$key][] = $value;
            } else {
                $this->configuration[$key] = [$this->configuration[$key], $value];
            }
        }
    }

    private static function parseDsn(Url $dsn): array
    {
        $data = ['host' => $dsn->getHost()];

        if (null !== $dsn->getScheme()) {
            $data['transport'] = $dsn->getScheme();
        }

        if (null !== $dsn->getUser()) {
            $data['username'] = $dsn->getUser();
        }

        if (null !== $dsn->getPassword()) {
            $data['password'] = $dsn->getPassword();
        }

        if (null !== $dsn->getUser() && null !== $dsn->getPassword()) {
            $data['auth_type'] = 'basic';
        }

        if (null !== $dsn->getPort()) {
            $data['port'] = $dsn->getPort();
        }

        if (null !== $dsn->getPath()) {
            $data['path'] = $dsn->getPath();
        }

        foreach ($dsn->getParameters() as $optionName => $optionValue) {
            if ('false' === $optionValue) {
                $optionValue = false;
            } elseif ('true' === $optionValue) {
                $optionValue = true;
            } elseif (\is_numeric($optionValue)) {
                $optionValue = (int) $optionValue;
            }

            $data[$optionName] = $optionValue;
        }

        return $data;
    }
}
