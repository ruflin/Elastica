<?php

namespace Elastica\Aggregation\Traits;

trait KeyedTrait
{
    /**
     * Setting the keyed flag to true associates a unique string key
     * with each bucket and returns the result as a hash rather than an array.
     *
     * @return $this
     */
    public function setKeyed(bool $keyed = true): self
    {
        return $this->setParam('keyed', $keyed);
    }
}
