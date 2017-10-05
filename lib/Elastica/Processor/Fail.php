<?php
namespace Elastica\Processor;

/**
 * Elastica Fail Processor.
 *
 * @author   Federico Panini <fpanini@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/fail-processor.html
 */
class Fail extends AbstractProcessor
{
    /**
     * Fail constructor.
     *
     * @param string $message
     */
    public function __construct(string $message)
    {
        $this->setMessage($message);
    }

    /**
     * Set Fail message.
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessage(string $message)
    {
        return $this->setParam('message', $message);
    }
}
