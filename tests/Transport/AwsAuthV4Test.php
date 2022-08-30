<?php

namespace Elastica\Test\Transport;

use Aws\Credentials\CredentialProvider;
use Aws\Credentials\Credentials;
use Aws\Sdk;
use Elastica\Exception\Connection\GuzzleException;
use GuzzleHttp\Exception\ConnectException;

/**
 * @internal
 */
class AwsAuthV4Test extends GuzzleTest
{
    public static function setUpbeforeClass(): void
    {
        if (!\class_exists(Sdk::class)) {
            self::markTestSkipped('aws/aws-sdk-php package should be installed to run SignatureV4 transport tests');
        }
    }

    /**
     * @group unit
     */
    public function testSignsWithProvidedCredentialProvider(): void
    {
        $config = [
            'persistent' => false,
            'transport' => 'AwsAuthV4',
            'aws_credential_provider' => CredentialProvider::fromCredentials(
                new Credentials('foo', 'bar', 'baz')
            ),
            'aws_region' => 'us-east-1',
        ];

        $client = $this->_getClient($config);

        try {
            $client->request('_stats');
        } catch (GuzzleException $e) {
            $guzzleException = $e->getGuzzleException();
            $this->assertInstanceOf(ConnectException::class, $guzzleException);
            $request = $guzzleException->getRequest();
            $expected = 'AWS4-HMAC-SHA256 Credential=foo/'
                .\date('Ymd').'/us-east-1/es/aws4_request, ';
            $this->assertStringStartsWith(
                $expected,
                $request->getHeaderLine('Authorization')
            );
            $this->assertSame(
                'baz',
                $request->getHeaderLine('X-Amz-Security-Token')
            );
        }
    }

    /**
     * @group unit
     */
    public function testPrefersCredentialProviderToHardCodedCredentials(): void
    {
        $config = [
            'persistent' => false,
            'transport' => 'AwsAuthV4',
            'aws_credential_provider' => CredentialProvider::fromCredentials(
                new Credentials('foo', 'bar', 'baz')
            ),
            'aws_access_key_id' => 'snap',
            'aws_secret_access_key' => 'crackle',
            'aws_session_token' => 'pop',
            'aws_region' => 'us-east-1',
        ];

        $client = $this->_getClient($config);

        try {
            $client->request('_stats');
        } catch (GuzzleException $e) {
            $guzzleException = $e->getGuzzleException();
            $this->assertInstanceOf(ConnectException::class, $guzzleException);
            $request = $guzzleException->getRequest();
            $expected = 'AWS4-HMAC-SHA256 Credential=foo/'
                .\date('Ymd').'/us-east-1/es/aws4_request, ';
            $this->assertStringStartsWith(
                $expected,
                $request->getHeaderLine('Authorization')
            );
            $this->assertSame(
                'baz',
                $request->getHeaderLine('X-Amz-Security-Token')
            );
        }
    }

    /**
     * @group unit
     */
    public function testSignsWithProvidedCredentials(): void
    {
        $config = [
            'persistent' => false,
            'transport' => 'AwsAuthV4',
            'aws_access_key_id' => 'foo',
            'aws_secret_access_key' => 'bar',
            'aws_session_token' => 'baz',
            'aws_region' => 'us-east-1',
        ];

        $client = $this->_getClient($config);

        try {
            $client->request('_stats');
        } catch (GuzzleException $e) {
            $guzzleException = $e->getGuzzleException();
            $this->assertInstanceOf(ConnectException::class, $guzzleException);
            $request = $guzzleException->getRequest();
            $expected = 'AWS4-HMAC-SHA256 Credential=foo/'
                .\date('Ymd').'/us-east-1/es/aws4_request, ';
            $this->assertStringStartsWith(
                $expected,
                $request->getHeaderLine('Authorization')
            );
            $this->assertSame(
                'baz',
                $request->getHeaderLine('X-Amz-Security-Token')
            );
        }
    }

    public function testUseHttpAsDefaultProtocol(): void
    {
        $config = [
            'persistent' => false,
            'transport' => 'AwsAuthV4',
            'aws_access_key_id' => 'foo',
            'aws_secret_access_key' => 'bar',
            'aws_session_token' => 'baz',
            'aws_region' => 'us-east-1',
        ];
        $client = $this->_getClient($config);

        try {
            $client->request('_stats');
        } catch (GuzzleException $e) {
            $guzzleException = $e->getGuzzleException();
            $this->assertInstanceOf(ConnectException::class, $guzzleException);
            $request = $guzzleException->getRequest();

            $this->assertSame('http', $request->getUri()->getScheme());
        }
    }

    public function testSetHttpsIfItIsRequired(): void
    {
        $config = [
            'persistent' => false,
            'transport' => 'AwsAuthV4',
            'aws_access_key_id' => 'foo',
            'aws_secret_access_key' => 'bar',
            'aws_session_token' => 'baz',
            'aws_region' => 'us-east-1',
            'ssl' => true,
        ];
        $client = $this->_getClient($config);

        try {
            $client->request('_stats');
        } catch (GuzzleException $e) {
            $guzzleException = $e->getGuzzleException();
            $this->assertInstanceOf(ConnectException::class, $guzzleException);
            $request = $guzzleException->getRequest();

            $this->assertSame('https', $request->getUri()->getScheme());
        }
    }

    public function testSignsWithEnvironmentalCredentials(): void
    {
        $config = [
            'persistent' => false,
            'transport' => 'AwsAuthV4',
        ];
        \putenv('AWS_REGION=us-east-1');
        \putenv('AWS_ACCESS_KEY_ID=foo');
        \putenv('AWS_SECRET_ACCESS_KEY=bar');
        \putenv('AWS_SESSION_TOKEN=baz');

        $client = $this->_getClient($config);
        try {
            $client->request('_stats');
        } catch (GuzzleException $e) {
            $guzzleException = $e->getGuzzleException();
            $this->assertInstanceOf(ConnectException::class, $guzzleException);
            $request = $guzzleException->getRequest();
            $expected = 'AWS4-HMAC-SHA256 Credential=foo/'
                .\date('Ymd').'/us-east-1/es/aws4_request, ';
            $this->assertStringStartsWith(
                $expected,
                $request->getHeaderLine('Authorization')
            );
            $this->assertSame(
                'baz',
                $request->getHeaderLine('X-Amz-Security-Token')
            );
        }
    }

    /**
     * @group unit
     * @depends testSignsWithProvidedCredentials
     */
    public function testStripsTrailingDotInHost(): void
    {
        $host = $this->_getHost();
        $hostWithTrailingDot = $host.'.';

        $config = [
            'persistent' => false,
            'transport' => 'AwsAuthV4',
            'aws_access_key_id' => 'foo',
            'aws_secret_access_key' => 'bar',
            'aws_session_token' => 'baz',
            'aws_region' => 'us-east-1',
            'host' => $hostWithTrailingDot,
        ];
        $client = $this->_getClient($config);

        try {
            $client->request('_stats');
        } catch (GuzzleException $e) {
            $guzzleException = $e->getGuzzleException();
            $this->assertInstanceOf(ConnectException::class, $guzzleException);
            $request = $guzzleException->getRequest();
            $this->assertSame($host, $request->getHeader('host')[0]);
            $this->assertSame($hostWithTrailingDot, $request->getUri()->getHost());
        }
    }
}
