<?php

declare(strict_types=1);

namespace Elastica\Test\Transport\NodePool;

use Elastic\Transport\NodePool\Node;
use Elastic\Transport\NodePool\SimpleNodePool;

final class TraceableSimpleNodePool extends SimpleNodePool
{
    /**
     * Original method shuffle connections so it's a problem in tests.
     */
    public function setHosts(array $hosts): SimpleNodePool
    {
        $this->nodes = [];
        foreach ($hosts as $host) {
            $this->nodes[] = new Node($host);
        }

        $this->selector->setNodes($this->nodes);

        return $this;
    }

    /**
     * @return array<Node>
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }
}
