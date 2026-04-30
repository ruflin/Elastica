<?php

declare(strict_types=1);

namespace Elastica\Index;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Elastica\Exception\ClientException;
use Elastica\Exception\NotFoundException;
use Elastica\Index as BaseIndex;
use Elastica\Response;
use Elastica\ResponseConverter;

/**
 * Elastica index settings object.
 *
 * All settings listed in the update settings API (https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-update-settings.html)
 * can be changed on a running indices. To make changes like the merge policy (https://www.elastic.co/guide/en/elasticsearch/reference/current/index-modules-merge.html)
 * the index has to be closed first and reopened after the call
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-update-settings.html
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/index-modules-merge.html
 */
class Settings
{
    public const DEFAULT_REFRESH_INTERVAL = '1s';

    public const DEFAULT_NUMBER_OF_REPLICAS = 1;

    public const DEFAULT_NUMBER_OF_SHARDS = 1;

    /**
     * Response.
     *
     * @var Response Response object
     */
    protected $_response;

    /**
     * Stats info.
     *
     * @var array Stats info
     */
    protected $_data = [];

    /**
     * Index.
     *
     * @var BaseIndex Index object
     */
    protected BaseIndex $_index;

    /**
     * Construct.
     *
     * @param BaseIndex $index Index object
     */
    public function __construct(BaseIndex $index)
    {
        $this->_index = $index;
    }

    /**
     * Returns the current settings of the index.
     *
     * If param is set, only specified setting is return.
     * 'index.' is added in front of $setting.
     *
     * @param string $setting OPTIONAL Setting name to return
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     *
     * @return array|int|string|null Settings data
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-update-settings.html
     */
    public function get(string $setting = '', bool $includeDefaults = false)
    {
        $queryParameters = [
            'include_defaults' => $includeDefaults,
        ];

        $requestData = $this->getIndex()->getClient()->indices()->getSettings(
            \array_merge(['index' => $this->getIndex()->getName()], $queryParameters)
        )->asArray();

        $data = \reset($requestData);

        if (empty($data['settings']) || empty($data['settings']['index'])) {
            // should not append, the request should throw a ClientResponseException
            throw new NotFoundException('Index '.$this->getIndex()->getName().' not found');
        }

        $settings = $data['settings']['index'];
        $defaults = $data['defaults']['index'] ?? [];

        $settings = \array_merge($defaults, $settings);

        if (!$setting) {
            // return all array
            return $settings;
        }

        if (isset($settings[$setting])) {
            return $settings[$setting];
        }

        if (\str_contains($setting, '.')) {
            // translate old dot-notation settings to nested arrays
            $keys = \explode('.', $setting);
            foreach ($keys as $key) {
                if (isset($settings[$key])) {
                    $settings = $settings[$key];
                } else {
                    return null;
                }
            }

            return $settings;
        }

        return null;
    }

    /**
     * Returns a setting interpreted as a bool.
     *
     * One can use a real bool, int(0), int(1) to set bool settings.
     * But Elasticsearch stores and returns all settings as strings and does
     * not normalize bool values. This method ensures a bool is returned for
     * whichever string representation is used like 'true', '1', 'on', 'yes'.
     *
     * @param string $setting Setting name to return
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     */
    public function getBool(string $setting): bool
    {
        $data = $this->get($setting);

        return 'true' === $data || '1' === $data || 'on' === $data || 'yes' === $data;
    }

    /**
     * Sets the number of replicas.
     *
     * @param int $replicas Number of replicas
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     */
    public function setNumberOfReplicas(int $replicas): Response
    {
        return $this->set(['number_of_replicas' => $replicas]);
    }

    /**
     * Returns the number of replicas.
     *
     * If no number of replicas is set, the default number is returned
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     *
     * @return int The number of replicas
     */
    public function getNumberOfReplicas(): int
    {
        return (int) ($this->get('number_of_replicas') ?? self::DEFAULT_NUMBER_OF_REPLICAS);
    }

    /**
     * Returns the number of shards.
     *
     * If no number of shards is set, the default number is returned
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     *
     * @return int The number of shards
     */
    public function getNumberOfShards(): int
    {
        return (int) ($this->get('number_of_shards') ?? self::DEFAULT_NUMBER_OF_SHARDS);
    }

    /**
     * Sets the index to read only.
     *
     * @param bool $readOnly (default = true)
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     */
    public function setReadOnly(bool $readOnly = true): Response
    {
        return $this->set(['blocks.read_only' => $readOnly]);
    }

    /**
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     */
    public function getReadOnly(): bool
    {
        return $this->getBool('blocks.read_only');
    }

    /**
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     */
    public function getBlocksRead(): bool
    {
        return $this->getBool('blocks.read');
    }

    /**
     * @param bool $state OPTIONAL (default = true)
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     */
    public function setBlocksRead(bool $state = true): Response
    {
        return $this->set(['blocks.read' => $state]);
    }

    /**
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     */
    public function getBlocksWrite(): bool
    {
        return $this->getBool('blocks.write');
    }

    /**
     * @param bool $state OPTIONAL (default = true)
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     */
    public function setBlocksWrite(bool $state = true): Response
    {
        return $this->set(['blocks.write' => $state]);
    }

    /**
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     */
    public function getBlocksMetadata(): bool
    {
        // When blocks.metadata is enabled, reading the settings is not possible anymore.
        // So when a cluster_block_exception happened it must be enabled.
        try {
            return $this->getBool('blocks.metadata');
        } catch (ClientResponseException $e) {
            $response = ResponseConverter::toElastica($e->getResponse());
            if ('cluster_block_exception' === $response->getFullError()['type']) {
                return true;
            }

            throw $e;
        }
    }

    /**
     * Set to true to disable index metadata reads and writes.
     *
     * @param bool $state OPTIONAL (default = true)
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     */
    public function setBlocksMetadata(bool $state = true): Response
    {
        return $this->set(['blocks.metadata' => $state]);
    }

    /**
     * Sets the index refresh interval.
     *
     * Value can be for example 3s for 3 seconds or
     * 5m for 5 minutes. -1 to disabled refresh.
     *
     * @param string $interval Duration of the refresh interval
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     */
    public function setRefreshInterval(string $interval): Response
    {
        return $this->set(['refresh_interval' => $interval]);
    }

    /**
     * Returns the refresh interval.
     *
     * If no interval is set, the default interval is returned
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     *
     * @return string Refresh interval
     */
    public function getRefreshInterval(): string
    {
        return $this->get('refresh_interval') ?? self::DEFAULT_REFRESH_INTERVAL;
    }

    /**
     * Sets the specific merge policies.
     *
     * To have this changes made the index has to be closed and reopened
     *
     * @param string     $key   Merge policy key (for ex. expunge_deletes_allowed)
     * @param int|string $value
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/index-modules-merge.html
     */
    public function setMergePolicy(string $key, $value): Response
    {
        $this->_index->close();
        $response = $this->set(['merge.policy.'.$key => $value]);
        $this->_index->open();

        return $response;
    }

    /**
     * Returns the specific merge policy value.
     *
     * @param string $key Merge policy key (for ex. expunge_deletes_allowed)
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     *
     * @return int|string
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/index-modules-merge.html
     */
    public function getMergePolicy(string $key)
    {
        $settings = $this->get();

        return $settings['merge']['policy'][$key] ?? null;
    }

    /**
     * Can be used to set/update settings.
     *
     * @param array $data Arguments
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     */
    public function set(array $data): Response
    {
        $client = $this->getIndex()->getClient();

        if ($data) {
            $data = ['index' => $data];
        }

        return $client->toElasticaResponse(
            $client->indices()->putSettings(
                \array_merge(['index' => $this->getIndex()->getName(), 'body' => $data])
            )
        );
    }

    /**
     * Returns the index object.
     *
     * @return BaseIndex Index object
     */
    public function getIndex(): BaseIndex
    {
        return $this->_index;
    }
}
