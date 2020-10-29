<?php

namespace Elastica\Processor;

use Elastica\Param;

/**
 * Elastica Processor object.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/ingest-processors.html
 */
abstract class AbstractProcessor extends Param
{
    public function setTag(string $tag): self
    {
        return $this->setParam('tag', $tag);
    }

    /**
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/handling-failure-in-pipelines.html
     */
    public function setOnFailure(AbstractProcessor $processor): self
    {
        return $this->setParam('on_failure', $processor);
    }

    /**
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/handling-failure-in-pipelines.html
     */
    public function setIgnoreFailure(bool $ignoreFailure): self
    {
        return $this->setParam('ignore_failure', $ignoreFailure ? 'true' : 'false');
    }

    /**
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/ingest-conditionals.html
     */
    public function setIf(string $script): self
    {
        return $this->setParam('if', $script);
    }
}
