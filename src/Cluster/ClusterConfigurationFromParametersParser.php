<?php declare(strict_types = 1);

namespace Elastica\Cluster;

use Elastica\ClusterConfiguration;
use Elastica\ElasticSearchVersion;
use InvalidArgumentException;

class ClusterConfigurationFromParametersParser
{
    /**
     * @param mixed[] $parameters
     *
     * @return ClusterConfiguration[]
     *
     * @throws InvalidClusterConfigurationException
     */
    public function parseFromParameters(array $parameters): array
    {
        $clusters = [];

        foreach ($parameters as $clusterConfigurationData) {
            $createdCluster = $this->createClusterConfiguration($clusterConfigurationData);
            $clusters[$createdCluster->getId()] = $createdCluster;
        }

        return array_values($clusters);
    }


    /**
     * @param mixed[] $clusterConfigurationData
     *
     * @throws InvalidClusterConfigurationException
     */
    private function createClusterConfiguration(
        array $clusterConfigurationData
    ): ClusterConfiguration {
        $this->checkConfiguration($clusterConfigurationData);

        $host = (string)($clusterConfigurationData['haproxy']['host'] ?? $clusterConfigurationData['host']);
        $port = (int)($clusterConfigurationData['haproxy']['port'] ?? $clusterConfigurationData['port']);
        $version = $clusterConfigurationData['version'] ?? ElasticSearchVersion::VERSION_6;

        return new ClusterConfiguration(
            (string)$clusterConfigurationData['id'],
            $host,
            $port,
            ElasticSearchVersion::get((int)$version),
            $clusterConfigurationData['transport'] ?? null,
            $clusterConfigurationData['username'] ?? null,
            $clusterConfigurationData['password'] ?? null,
            $clusterConfigurationData['authType'] ?? null,
        );
    }


    /**
     * @param mixed[] $clusterConfigurationData
     *
     * @throws InvalidClusterConfigurationException
     */
    private function checkConfiguration(array $clusterConfigurationData): void
    {
        if (!isset($clusterConfigurationData['id'])) {
            throw InvalidClusterConfigurationException::byMissingId();
        }

        $clusterId = (string)$clusterConfigurationData['id'];

        // Be backward compatible, until haproxy field is removed from every config in infra-ansible
        if (isset($clusterConfigurationData['haproxy'])) {
            if (!isset($clusterConfigurationData['haproxy']['port'])) {
                throw InvalidClusterConfigurationException::byMissingField($clusterId, 'haproxy.port');
            }

            if (!isset($clusterConfigurationData['haproxy']['host'])) {
                throw InvalidClusterConfigurationException::byMissingField($clusterId, 'haproxy.host');
            }
        } else {
            if (!isset($clusterConfigurationData['port'])) {
                throw InvalidClusterConfigurationException::byMissingField($clusterId, 'port');
            }

            if (!isset($clusterConfigurationData['host'])) {
                throw InvalidClusterConfigurationException::byMissingField($clusterId, 'host');
            }
        }

        if (isset($clusterConfigurationData['version'])) {
            try {
                ElasticSearchVersion::get((int)$clusterConfigurationData['version']);
            } catch (InvalidArgumentException $e) {
                throw InvalidClusterConfigurationException::byInvalidElasticSearchVersion(
                    $clusterId,
                    (int)$clusterConfigurationData['version'],
                );
            }
        }
    }
}
