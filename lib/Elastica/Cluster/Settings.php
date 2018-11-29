<?php
namespace Elastica\Cluster;

use Elastica\Client;
use Elastica\Request;
use Elastica\Response;

/**
 * Cluster settings.
 *
 * @author   Nicolas Ruflin <spam@ruflin.com>
 *
 * @link     https://www.elastic.co/guide/en/elasticsearch/reference/current/cluster-update-settings.html
 */
class Settings
{
    /**
     * @var \Elastica\Client Client object
     */
    protected $_client;

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
        return $this->request()->getData();
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

        if (!empty($setting)) {
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

        if (!empty($setting)) {
            if (isset($settings[$setting])) {
                return $settings[$setting];
            }

            if (strpos($setting, '.') !== false) {
                // convert dot notation to nested arrays
                $keys = explode('.', $setting);
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
     *
     * @param string $key
     * @param string $value
     *
     * @return Response
     */
    public function setPersistent(string $key, string $value): Response
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
     *
     * @param string $key
     * @param string $value
     *
     * @return Response
     */
    public function setTransient(string $key, string $value): Response
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
     * @param bool $readOnly
     * @param bool $persistent
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
     *
     * @return Response
     */
    public function set(array $settings): Response
    {
        return $this->request($settings, Request::PUT);
    }

    /**
     * Get the client.
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->_client;
    }

    /**
     * Sends settings request.
     *
     * @param array  $data   OPTIONAL Data array
     * @param string $method OPTIONAL Transfer method (default = \Elastica\Request::GET)
     *
     * @return Response Response object
     */
    public function request(array $data = [], string $method = Request::GET): Response
    {
        $path = '_cluster/settings';

        return $this->getClient()->request($path, $method, $data);
    }
}
