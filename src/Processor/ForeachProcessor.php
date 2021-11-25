<?php

namespace Elastica\Processor;

/**
 * Elastica Foreach Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 * @author Thibaut Simon-Fine <tsimonfine@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/foreach-processor.html
 */
class ForeachProcessor extends AbstractProcessor
{
    use Traits\FieldTrait;
    use Traits\IgnoreFailureTrait;
    use Traits\IgnoreMissingTrait;

    public const DEFAULT_IGNORE_MISSING_VALUE = false;

    /**
     * @param AbstractProcessor|array $processor
     */
    public function __construct(string $field, $processor)
    {
        $this->setField($field);

        if ($processor instanceof AbstractProcessor) {
            $this->setProcessor($processor);
        } elseif (\is_array($processor)) {
            $this->setRawProcessor($processor);
        } else {
            throw new \TypeError(\sprintf('Argument 2 passed to %s::__construct() must be of type %s|array, %s given.', self::class, AbstractProcessor::class, \is_object($processor) ? \get_class($processor) : \gettype($processor)));
        }
    }

    /**
     * Set processor.
     *
     * @return $this
     */
    public function setProcessor(AbstractProcessor $processor): self
    {
        return $this->setParam('processor', $processor);
    }

    /**
     * Set raw processor.
     * Example : ['remove' => ['field' => 'user_agent']].
     *
     * @return $this
     */
    public function setRawProcessor(array $processor): self
    {
        return $this->setParam('processor', $processor);
    }
}
