<?php

namespace Elastica\Processor;

/**
 * Elastica Fail Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/fail-processor.html
 */
class Fail extends AbstractProcessor
{
    /**
     * Fail constructor.
     */
    public function __construct(string $message)
    {
        $this->setMessage($message);
    }

    /**
     * Set Fail message.
     *
     * @return $this
     */
    public function setMessage(string $message): self
    {
        return $this->setParam('message', $message);
    }
}
