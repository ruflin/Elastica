<?php

declare(strict_types=1);

namespace Elastica;

use Elastica\Exception\InvalidException;

/**
 * Elastica client configuration.
 *
 * @author Antoine Lamirault <lamiraultantoine@gmail.com>
 */
class ClientConfiguration
{
    public const DEFAULT_HOST = 'localhost:9200';

    /**
     * Config with defaults.
     *
     * retryOnConflict: Use in \Elastica\Client::updateDocument
     * bigintConversion: Set to true to enable the JSON bigint to string conversion option (see issue #717)
     *
     * @var array{
     *     hosts: list<string>,
     *     retryOnConflict: int,
     *     username: string|null,
     *     password: string|null,
     *     transport_config: array<array-key, mixed>,
     * }
     */
    protected array $configuration = [
        'hosts' => [self::DEFAULT_HOST],
        'retryOnConflict' => 0,
        'username' => null,
        'password' => null,
        'transport_config' => [], // http_client, http_client_config, http_client_options, node_pool
    ];

    /**
     * Create configuration.
     *
     * @param array $config Additional config
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
     * Returns a specific config key or the whole config array if not set.
     *
     * @throws InvalidException if the given key is not found in the configuration
     *
     * @return mixed Config value
     */
    public function get(string $key)
    {
        if ('' === $key) {
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
