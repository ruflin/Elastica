<?php declare(strict_types = 1);

namespace Elastica\Cluster;

use Elastica\ClusterConfiguration;
use LogicException;
use RuntimeException;
use function assert;
use function current;
use function sprintf;

class ClusterConfigurationProvider
{
    /**
     * @var ClusterConfiguration[]
     */
    private array $clusterConfigurations;


    /**
     * @param ClusterConfiguration[] $clusterConfigurations
     */
    public function __construct(array $clusterConfigurations)
    {
        foreach ($clusterConfigurations as $clusterConfiguration) {
            if (!$clusterConfiguration instanceof ClusterConfiguration::class) {
                $message = sprintf('Cluster configuration is not instance of %s', ClusterConfiguration::class);
                throw new LogicException($message);
            }
        }
        $this->clusterConfigurations = $clusterConfigurations;
    }


    /**
     * @return ClusterConfiguration[]
     */
    public function getAllAvailableClusterConfigurations(): array
    {
        return $this->clusterConfigurations;
    }


    public function getFirstConfigurationWithMatchingVersion(int $elasticSearchVersion): ClusterConfiguration
    {
        foreach ($this->clusterConfigurations as $configuration) {
            if ($configuration->getVersion()->is($elasticSearchVersion)) {
                return $configuration;
            }
        }

        throw new RuntimeException(sprintf(
            'No cluster configuration with elastic search version %d was found',
            $elasticSearchVersion,
        ));
    }


    public function hasClusterConfiguration(string $clusterId): bool
    {
        foreach ($this->clusterConfigurations as $clusterConfiguration) {
            if ($clusterConfiguration->getId() === $clusterId) {
                return true;
            }
        }

        return false;
    }


    public function getClusterConfiguration(string $clusterId, bool $throwIfNotFound = false): ClusterConfiguration
    {
        if ($throwIfNotFound && !$this->hasClusterConfiguration($clusterId)) {
            throw ClusterConfigurationNotFoundException::byClusterId($clusterId);
        }

        foreach ($this->clusterConfigurations as $clusterConfiguration) {
            if ($clusterConfiguration->getId() === $clusterId) {
                return $clusterConfiguration;
            }
        }

        $firstConfiguration = current($this->clusterConfigurations);
        assert($firstConfiguration instanceof ClusterConfiguration);

        return $firstConfiguration;
    }
}
