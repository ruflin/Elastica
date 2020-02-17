<?php

namespace Elastica\Transport;

use Aws\Credentials\CredentialProvider;
use Aws\Credentials\Credentials;
use Aws\Signature\SignatureV4;
use Elastica\Connection;
use GuzzleHttp;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

class AwsAuthV4 extends Guzzle
{
    protected function _getGuzzleClient(bool $persistent = true): Client
    {
        if (!$persistent || !self::$_guzzleClientConnection) {
            $stack = HandlerStack::create(GuzzleHttp\choose_handler());
            $stack->push($this->getSigningMiddleware(), 'sign');

            self::$_guzzleClientConnection = new Client([
                'handler' => $stack,
            ]);
        }

        return self::$_guzzleClientConnection;
    }

    protected function _getBaseUrl(Connection $connection): string
    {
        $this->initializePortAndScheme();

        return parent::_getBaseUrl($connection);
    }

    private function getSigningMiddleware(): callable
    {
        $region = $this->getConnection()->hasParam('aws_region')
            ? $this->getConnection()->getParam('aws_region')
            : \getenv('AWS_REGION');
        $signer = new SignatureV4('es', $region);
        $credProvider = $this->getCredentialProvider();

        return Middleware::mapRequest(function (RequestInterface $req) use (
            $signer,
            $credProvider
        ) {
            return $signer->signRequest($req, $credProvider()->wait());
        });
    }

    private function getCredentialProvider(): callable
    {
        $connection = $this->getConnection();

        if ($connection->hasParam('aws_credential_provider')) {
            return $connection->getParam('aws_credential_provider');
        }

        if ($connection->hasParam('aws_secret_access_key')) {
            return CredentialProvider::fromCredentials(new Credentials(
                $connection->getParam('aws_access_key_id'),
                $connection->getParam('aws_secret_access_key'),
                $connection->hasParam('aws_session_token')
                    ? $connection->getParam('aws_session_token')
                    : null
            ));
        }

        return CredentialProvider::defaultProvider();
    }

    private function initializePortAndScheme(): void
    {
        $connection = $this->getConnection();
        if (true === $this->isSslRequired($connection)) {
            $this->_scheme = 'https';
            $connection->setPort(443);
        } else {
            $this->_scheme = 'http';
            $connection->setPort(80);
        }
    }

    private function isSslRequired(Connection $conn, bool $default = false): bool
    {
        return $conn->hasParam('ssl')
            ? (bool) $conn->getParam('ssl')
            : $default;
    }
}
