<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Log;
use Elastica\Test\Base as BaseTest;

class LogTest extends BaseTest
{
    public function testSetLogConfigPath()
    {
        $logPath = '/tmp/php.log';
        $client = new Client(array('log' => $logPath));
        $this->assertEquals($logPath, $client->getConfig('log'));
    }

    public function testSetLogConfigEnable()
    {
        $client = new Client(array('log' => true));
        $this->assertTrue($client->getConfig('log'));
    }

    public function testEmptyLogConfig()
    {
        $client = $this->_getClient();
        $this->assertEmpty($client->getConfig('log'));
    }

    public function testGetLastMessage()
    {
        $log = new Log('/tmp/php.log');
        $message = 'hello world';

        $log->log($message);

        $this->assertEquals($message, $log->getLastMessage());
    }

    public function testGetLastMessage2()
    {
        $client = new Client(array('log' => true));
        $log = new Log($client);

        // Set log path temp path as otherwise test fails with output
        $errorLog = ini_get('error_log');
        ini_set('error_log', sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'php.log');

        $message = 'hello world';

        $log->log($message);

        ini_set('error_log', $errorLog);

        $this->assertEquals($message, $log->getLastMessage());
    }
}
