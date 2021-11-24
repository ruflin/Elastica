<?php

namespace Elastica\Processor\Traits;

trait IgnoreMissingTrait
{
    /**
     * Set "ignore_missing" option.
     *
     * @return $this
     */
    public function setIgnoreMissing(bool $ignoreMissing): self
    {
        return $this->setParam('ignore_missing', $ignoreMissing);
    }
}
