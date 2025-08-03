<?php

declare(strict_types=1);

namespace Elastica\Cluster;

use Elastica\Client;
use Elastica\Response;

/**
 * Cluster settings.
 *
 * @author   Nicolas Ruflin <spam@ruflin.com>
 *
 * @see     https://www.elastic.co/guide/en/elasticsearch/reference/current/cluster-update-settings.html
 */
class Settings
{
    /**
     * @var Client Client object
     */
    protected Client $_client;

    /**
     * Creates a cluster object.
     *
     * @param Client $client Connection client object
     */
    public function __construct(Client $client)
    {
        $this->_client = $client;
    }

    /**
     * Returns settings data.
     *
     * @return array Settings data (persistent and transient)
     */
    public function get(): array
    {
        return $this->_client->cluster()->getSettings()->asArray();
    }

    /**
     * Returns the current persistent settings of the cluster.
     *
     * If param is set, only specified setting is return.
     *
     * @param string $setting OPTIONAL Setting name to return
     *
     * @return array|string|null Settings data
     */
    public function getPersistent(string $setting = '')
    {
        $data = $this->get();
        $settings = $data['persistent'];

        if ('' !== $setting) {
            return $settings[$setting] ?? null;
        }

        return $settings;
    }

    /**
     * Returns the current transient settings of the cluster.
     *
     * If param is set, only specified setting is return.
     *
     * @param string $setting OPTIONAL Setting name to return
     *
     * @return array|string|null Settings data
     */
    public function getTransient(string $setting = '')
    {
        $data = $this->get();
        $settings = $data['transient'];

        if ('' !== $setting) {
            if (isset($settings[$setting])) {
                return $settings[$setting];
            }

            if (\str_contains($setting, '.')) {
                // convert dot notation to nested arrays
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

        return $settings;
    }

    /**
     * Sets persistent setting.
     */
    public function setPersistent(string $key, $value): Response
    {
        return $this->set(
            [
                'persistent' => [
                    $key => $value,
                ],
            ]
        );
    }

    /**
     * Sets transient settings.
     */
    public function setTransient(string $key, $value): Response
    {
        return $this->set(
            [
                'transient' => [
                    $key => $value,
                ],
            ]
        );
    }

    /**
     * Sets the cluster to read only.
     *
     * Second param can be used to set it persistent
     *
     * @return Response $response
     */
    public function setReadOnly(bool $readOnly = true, bool $persistent = false): Response
    {
        $key = 'cluster.blocks.read_only';

        if ($persistent) {
            return $this->setPersistent($key, $readOnly);
        }

        return $this->setTransient($key, $readOnly);
    }

    /**
     * Set settings for cluster.
     *
     * @param array $settings Raw settings (including persistent or transient)
     */
    public function set(array $settings): Response
    {
        return $this->_client->toElasticaResponse(
            $this->_client->cluster()->putSettings(['body' => $settings])
        );
    }

    /**
     * Get the client.
     */
    public function getClient(): Client
    {
        return $this->_client;
    }
}
