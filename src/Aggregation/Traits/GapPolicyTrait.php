<?php

declare(strict_types=1);

namespace Elastica\Aggregation\Traits;

use Elastica\Aggregation\GapPolicyInterface;

/**
 * @see GapPolicyInterface
 */
trait GapPolicyTrait
{
    public function setGapPolicy(string $gapPolicy = GapPolicyInterface::SKIP): self
    {
        return $this->setParam('gap_policy', $gapPolicy);
    }
}
