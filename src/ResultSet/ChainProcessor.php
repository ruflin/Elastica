<?php

declare(strict_types=1);

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
    private array $processors;

    /**
     * @param ProcessorInterface[] $processors
     */
    public function __construct(array $processors)
    {
        $this->processors = $processors;
    }

    public function process(ResultSet $resultSet): void
    {
        foreach ($this->processors as $processor) {
            $processor->process($resultSet);
        }
    }
}
