<?php

namespace Elastica;

/**
 * Elastica log object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Log
{
    /**
     * Log path or true if enabled
     *
     * @var string|bool
     */
    protected $_log = true;

    /**
     * Last logged message
     *
     * @var string Last logged message
     */
    protected $_lastMessage = '';

    /**
     * Inits log object
     *
     * @param string|bool String to set a specific file for logging
     */
    public function __construct($log = '')
    {
        $this->setLog($log);
    }

    /**
     * Log a message
     *
     * @param string|\Elastica\Request $message
     */
    public function log($message)
    {
        if ($message instanceof Request) {
            $message = $message->toString();
        }

        $this->_lastMessage = $message;

        if (!empty($this->_log) && is_string($this->_log)) {
            error_log($message . PHP_EOL, 3, $this->_log);
        } else {
            error_log($message);
        }

    }

    /**
     * Enable/disable log or set log path
     *
     * @param  bool|string  $log Enables log or sets log path
     * @return \Elastica\Log
     */
    public function setLog($log)
    {
        $this->_log = $log;

        return $this;
    }

    /**
     * Return last logged message
     *
     * @return string Last logged message
     */
    public function getLastMessage()
    {
        return $this->_lastMessage;
    }
}
