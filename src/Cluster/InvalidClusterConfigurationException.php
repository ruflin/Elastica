<?php declare(strict_types = 1);

namespace Elastica\Cluster;

use Exception;
use function sprintf;

class InvalidClusterConfigurationException extends Exception
{
    public static function byMissingField(string $clusterId, string $fieldName): self
    {
        return new self(
            sprintf(
                'Invalid configuration for ElasticSearch cluster with id %s - "%s" field is missing',
                $clusterId,
                $fieldName,
            ),
        );
    }


    public static function byMissingId(): self
    {
        return new self('Invalid configuration for ElasticSearch cluster - "id" field is missing');
    }


    public static function byInvalidElasticSearchVersion(string $clusterId, int $elasticSearchVersion): self
    {
        return new self(
            sprintf(
                'Invalid configuration for ElasticSearch cluster with id %s - "%s" is not valid version',
                $clusterId,
                $elasticSearchVersion,
            ),
        );
    }
}
