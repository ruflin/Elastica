<?php

declare(strict_types=1);

namespace Elastica\Aggregation\Traits;

trait MissingTrait
{
    /**
     * Defines how documents that are missing a value should be treated.
     *
     * @return $this
     */
    public function setMissing($missing): self
    {
        return $this->setParam('missing', $missing);
    }
}
