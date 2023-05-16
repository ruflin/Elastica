<?php declare(strict_types = 1);

namespace Elastica;

use Elastica\Cluster\ClusterConfiguration;
use Psr\Log\LoggerInterface;

class ClientFactory
{
    private ServerConfiguration $serverConfiguration;

    private RequestCounter $requestCounter;

    private LoggerInterface $lazyLogger;

    private bool $isRequestLoggingEnabled;


    public function __construct(
        ServerConfiguration $serverConfiguration,
        RequestCounter $requestCounter,
        LoggerInterface $lazyLogger,
        bool $isRequestLoggingEnabled
    ) {
        $this->serverConfiguration = $serverConfiguration;
        $this->requestCounter = $requestCounter;
        $this->lazyLogger = $lazyLogger;
        $this->isRequestLoggingEnabled = $isRequestLoggingEnabled;
    }


    public function createClientForCluster(
        ClusterConfiguration $clusterConfiguration,
        callable $isBrandIndependentIndexByName,
        bool $withRequestCounter = true,
        int $loggingMode = Client::LOG_DISABLED
    ): Client {
        $serverConfiguration = $this->serverConfiguration->getConfiguration($clusterConfiguration);

        $config = [
            'servers' => [$serverConfiguration],
            'apiVersion' => $clusterConfiguration->getVersion()->getValue(),
            'documentTypeResolver' => static fn(string $indexName): string =>
                $isBrandIndependentIndexByName($indexName) ? Type::DOC : Type::DEFAULT,
        ];

        $client = new Client(
            $config,
            null,
            null,
            $withRequestCounter ? $this->requestCounter : null,
        );

        $client->setLoggingMode($loggingMode);

        if ($this->isRequestLoggingEnabled) {
            $client->setLogger($this->lazyLogger);
        }

        return $client;
    }
}

