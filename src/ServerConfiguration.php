<?php declare(strict_types = 1);

namespace Elastica;

use Elastica\Cluster\ClusterConfiguration;

class ServerConfiguration
{
    private int $timeout;

    private int $connectTimeout;


    public function __construct(int $timeout, int $connectTimeout)
    {
        $this->timeout = $timeout;
        $this->connectTimeout = $connectTimeout;
    }


    /**
     * @return array{
     *     host: string,
     *     port: int,
     *     transport: string|null,
     *     username: string|null,
     *     password: string|null,
     *     auth_type: string|null,
     *     timeout: int,
     *     connectTimeout: int,
     *     bigintConversion: true
     * }
     *
     * @see Client::$_config
     */
    public function getConfiguration(ClusterConfiguration $clusterConfiguration): array
    {
        return [
            'host' => $clusterConfiguration->getHost(),
            'port' => $clusterConfiguration->getPort(),
            'transport' => $clusterConfiguration->getTransport(),
            'username' => $clusterConfiguration->getUsername(),
            'password' => $clusterConfiguration->getPassword(),
            'auth_type' => $clusterConfiguration->getAuthType(),
            'timeout' => $this->timeout,
            'connectTimeout' => $this->connectTimeout,
            'bigintConversion' => true,
        ];
    }
}
