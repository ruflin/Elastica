<?php declare(strict_types = 1);

namespace Elastica\Cluster;

use Elastica\ElasticSearchVersion;

class ClusterConfiguration
{
    private string $id;

    private string $host;

    private int $port;

    private ?string $transport = null;

    private ?string $username = null;

    private ?string $password = null;

    private ?string $authType = null;

    private ElasticSearchVersion $version;


    public function __construct(
        string $id,
        string $host,
        int $port,
        ElasticSearchVersion $version,
        ?string $transport = null,
        ?string $username = null,
        ?string $password = null,
        ?string $authType = null
    ) {
        $this->id = $id;
        $this->host = $host;
        $this->port = $port;
        $this->version = $version;
        $this->transport = $transport;
        $this->username = $username;
        $this->password = $password;
        $this->authType = $authType;
    }


    public function getId(): string
    {
        return $this->id;
    }


    public function getHost(): string
    {
        return $this->host;
    }


    public function getPort(): int
    {
        return $this->port;
    }


    public function getVersion(): ElasticSearchVersion
    {
        return $this->version;
    }


    public function getTransport(): ?string
    {
        return $this->transport;
    }


    public function getUsername(): ?string
    {
        return $this->username;
    }


    public function getPassword(): ?string
    {
        return $this->password;
    }


    public function getAuthType(): ?string
    {
        return $this->authType;
    }
}
