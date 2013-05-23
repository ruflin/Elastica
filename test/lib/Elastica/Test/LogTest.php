<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Log;
use Elastica\Test\Base as BaseTest;
use Psr\Log\LogLevel;

class LogTest extends BaseTest
{
    private $context = array();
    private $message = 'hello world';

    public function testLogInterface()
    {
        $log = new Log();
        $this->assertInstanceOf('Psr\Log\LoggerInterface', $log);
    }

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

    public function testSetLogConfigEnable1()
    {
        $client = new Client();
        $client->setLogger(new Log());
        $this->assertFalse($client->getConfig('log'));
    }

    public function testEmptyLogConfig()
    {
        $client = $this->_getClient();
        $this->assertEmpty($client->getConfig('log'));
    }

    public function testGetLastMessage()
    {
        $log = new Log('/tmp/php.log');

        $log->log(LogLevel::DEBUG, $this->message, $this->context);

        $this->context['error_message'] = $this->message;
        $message = json_encode($this->context);

        $this->assertEquals($message, $log->getLastMessage());
    }

    public function testGetLastMessage2()
    {
        $client = new Client(array('log' => true));
        $log = new Log($client);

        // Set log path temp path as otherwise test fails with output
        $errorLog = ini_get('error_log');
        ini_set('error_log', sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'php.log');

        $this->context['error_message'] = $this->message;
        $message = json_encode($this->context);

        $log->log(LogLevel::DEBUG, $this->message, $this->context);

        ini_set('error_log', $errorLog);

        $this->assertEquals($message, $log->getLastMessage());
    }

    public function testGetLastMessageInfo()
    {
        $log = $this->initLog();
        $log->info($this->message, $this->context);
        $this->assertEquals($this->getMessage(), $log->getLastMessage());
    }

    public function testGetLastMessageCritical()
    {
        $log = $this->initLog();
        $log->critical($this->message, $this->context);
        $this->assertEquals($this->getMessage(), $log->getLastMessage());
    }


    public function testGetLastMessageAlert()
    {
        $log = $this->initLog();
        $log->alert($this->message, $this->context);
        $this->assertEquals($this->getMessage(), $log->getLastMessage());
    }

    public function testGetLastMessageDebug()
    {
        $log = $this->initLog();
        $log->debug($this->message, $this->context);
        $this->assertEquals($this->getMessage(), $log->getLastMessage());
    }

    public function testGetLastMessageEmergency()
    {
        $log = $this->initLog();
        $log->emergency($this->message, $this->context);
        $this->assertEquals($this->getMessage(), $log->getLastMessage());
    }

    public function testGetLastMessageError()
    {
        $log = $this->initLog();
        $log->error($this->message, $this->context);
        $this->assertEquals($this->getMessage(), $log->getLastMessage());
    }

    public function testGetLastMessageNotice()
    {
        $log = $this->initLog();
        $log->notice($this->message, $this->context);
        $this->assertEquals($this->getMessage(), $log->getLastMessage());
    }

    public function testGetLastMessageWarning()
    {
        $log = $this->initLog();
        $log->warning($this->message, $this->context);
        $this->assertEquals($this->getMessage(), $log->getLastMessage());
    }

    private function initLog()
    {
        $log = new Log('/tmp/php.log');

        return $log;
    }

    private function getMessage()
    {
        $this->context['error_message'] = $this->message;

        return json_encode($this->context);
    }
}
