<?php

namespace Elastica\Test\Transport;

use Elastica\Exception\Connection\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class AwsAuthV4Test extends GuzzleTest
{
    public static function setUpBeforeClass()
    {
        if (!class_exists('Aws\\Sdk')) {
            self::markTestSkipped('aws/aws-sdk-php package should be installed to run SignatureV4 transport tests');
        }
    }

    /**
     * @group unit
     */
    public function testSignsWithProvidedCredentials()
    {
        $config = array(
            'persistent' => false,
            'transport' => 'AwsAuthV4',
            'aws_access_key_id' => 'foo',
            'aws_secret_access_key' => 'bar',
            'aws_session_token' => 'baz',
            'aws_region' => 'us-east-1',
        );

        $client = $this->_getClient($config);
        try {
            $client->request('_status', 'GET');
        } catch (GuzzleException $e) {
            $guzzleException = $e->getGuzzleException();
            if ($guzzleException instanceof RequestException) {
                $request = $guzzleException->getRequest();
                $expected = 'AWS4-HMAC-SHA256 Credential=foo/'
                    .date('Ymd').'/us-east-1/es/aws4_request, ';
                $this->assertStringStartsWith(
                    $expected,
                    $request->getHeaderLine('Authorization')
                );
                $this->assertSame(
                    'baz',
                    $request->getHeaderLine('X-Amz-Security-Token')
                );
            } else {
                throw $e;
            }
        }
    }

    public function testSignsWithEnvironmentalCredentials()
    {
        $config = array(
            'persistent' => false,
            'transport' => 'AwsAuthV4',
        );
        putenv('AWS_REGION=us-east-1');
        putenv('AWS_ACCESS_KEY_ID=foo');
        putenv('AWS_SECRET_ACCESS_KEY=bar');
        putenv('AWS_SESSION_TOKEN=baz');

        $client = $this->_getClient($config);
        try {
            $client->request('_status', 'GET');
        } catch (GuzzleException $e) {
            $guzzleException = $e->getGuzzleException();
            if ($guzzleException instanceof RequestException) {
                $request = $guzzleException->getRequest();
                $expected = 'AWS4-HMAC-SHA256 Credential=foo/'
                    .date('Ymd').'/us-east-1/es/aws4_request, ';
                $this->assertStringStartsWith(
                    $expected,
                    $request->getHeaderLine('Authorization')
                );
                $this->assertSame(
                    'baz',
                    $request->getHeaderLine('X-Amz-Security-Token')
                );
            } else {
                throw $e;
            }
        }
    }
}
