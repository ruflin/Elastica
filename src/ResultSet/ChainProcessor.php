<?php

namespace Elastica\ResultSet;

use Elastica\ResultSet;

/**
 * Allows multiple ProcessorInterface instances to operate on the same
 * ResultSet, calling each in turn.
 */
class ChainProcessor implements ProcessorInterface
{
    /**
     * @var ProcessorInterface[]
     */
    private $processors;

    /**
     * @param ProcessorInterface[] $processors
     */
    public function __construct(array $processors)
    {
        $this->processors = $processors;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ResultSet $resultSet): void
    {
        foreach ($this->processors as $processor) {
            $processor->process($resultSet);
        }
    }
}
