<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Log;
use Elastica\Test\Base as BaseTest;
use Psr\Log\LogLevel;

class LogTest extends BaseTest
{
    private $_context = array();
    private $_message = 'hello world';

    protected function setUp()
    {
        if (!class_exists('Psr\Log\AbstractLogger')) {
            $this->markTestSkipped('The Psr extension is not available.');
        }
    }

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

        $log->log(LogLevel::DEBUG, $this->_message, $this->_context);

        $this->_context['error_message'] = $this->_message;
        $message = json_encode($this->_context);

        $this->assertEquals($message, $log->getLastMessage());
    }

    public function testGetLastMessage2()
    {
        $client = new Client(array('log' => true));
        $log = new Log($client);

        // Set log path temp path as otherwise test fails with output
        $errorLog = ini_get('error_log');
        ini_set('error_log', sys_get_temp_dir().DIRECTORY_SEPARATOR.'php.log');

        $this->_context['error_message'] = $this->_message;
        $message = json_encode($this->_context);

        $log->log(LogLevel::DEBUG, $this->_message, $this->_context);
        ini_set('error_log', $errorLog);

        $this->assertEquals($message, $log->getLastMessage());
    }

    public function testGetLastMessageInfo()
    {
        $log = $this->initLog();
        $log->info($this->_message, $this->_context);
        $this->assertEquals($this->getMessage(), $log->getLastMessage());
    }

    public function testGetLastMessageCritical()
    {
        $log = $this->initLog();
        $log->critical($this->_message, $this->_context);
        $this->assertEquals($this->getMessage(), $log->getLastMessage());
    }

    public function testGetLastMessageAlert()
    {
        $log = $this->initLog();
        $log->alert($this->_message, $this->_context);
        $this->assertEquals($this->getMessage(), $log->getLastMessage());
    }

    public function testGetLastMessageDebug()
    {
        $log = $this->initLog();
        $log->debug($this->_message, $this->_context);
        $this->assertEquals($this->getMessage(), $log->getLastMessage());
    }

    public function testGetLastMessageEmergency()
    {
        $log = $this->initLog();
        $log->emergency($this->_message, $this->_context);
        $this->assertEquals($this->getMessage(), $log->getLastMessage());
    }

    public function testGetLastMessageError()
    {
        $log = $this->initLog();
        $log->error($this->_message, $this->_context);
        $this->assertEquals($this->getMessage(), $log->getLastMessage());
    }

    public function testGetLastMessageNotice()
    {
        $log = $this->initLog();
        $log->notice($this->_message, $this->_context);
        $this->assertEquals($this->getMessage(), $log->getLastMessage());
    }

    public function testGetLastMessageWarning()
    {
        $log = $this->initLog();
        $log->warning($this->_message, $this->_context);
        $this->assertEquals($this->getMessage(), $log->getLastMessage());
    }

    private function initLog()
    {
        $log = new Log('/tmp/php.log');

        return $log;
    }

    private function getMessage()
    {
        $this->_context['error_message'] = $this->_message;

        return json_encode($this->_context);
    }
}
