<?php declare(strict_types = 1);

namespace Elastica\Cluster;

use RuntimeException;
use function sprintf;

class ClusterConfigurationNotFoundException extends RuntimeException
{
    public static function byClusterId(string $clusterId): self
    {
        return new self(
            sprintf('ElasticSearch cluster configuration with id %s was not found', $clusterId),
        );
    }
}
