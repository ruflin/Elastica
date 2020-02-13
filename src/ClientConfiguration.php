<?php

namespace Elastica;

use Elastica\Exception\InvalidException;

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
        'connections' => [], // host, port, path, transport, compression, persistent, timeout, username, password, config -> (curl, headers, url)
        'roundRobin' => false,
        'retryOnConflict' => 0,
        'bigintConversion' => false,
        'username' => null,
        'password' => null,
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
     * Create configuration from Dsn string.
     *
     * @return ClientConfiguration
     */
    public static function fromDsn(string $dsn): self
    {
        if (false === $parsedDsn = \parse_url($dsn)) {
            throw new InvalidException(\sprintf("DSN '%s' is invalid.", $dsn));
        }

        $clientConfiguration = new static();

        if (isset($parsedDsn['scheme'])) {
            $clientConfiguration->set('transport', $parsedDsn['scheme']);
        }

        if (isset($parsedDsn['host'])) {
            $clientConfiguration->set('host', $parsedDsn['host']);
        }

        if (isset($parsedDsn['user'])) {
            $clientConfiguration->set('username', \urldecode($parsedDsn['user']));
        }

        if (isset($parsedDsn['pass'])) {
            $clientConfiguration->set('password', \urldecode($parsedDsn['pass']));
        }

        if (isset($parsedDsn['port'])) {
            $clientConfiguration->set('port', $parsedDsn['port']);
        }

        if (isset($parsedDsn['path'])) {
            $clientConfiguration->set('path', $parsedDsn['path']);
        }

        $options = [];
        if (isset($parsedDsn['query'])) {
            \parse_str($parsedDsn['query'], $options);
        }

        foreach ($options as $optionName => $optionValue) {
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
}
