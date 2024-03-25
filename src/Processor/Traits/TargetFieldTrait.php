<?php

declare(strict_types=1);

namespace Elastica\Processor\Traits;

trait TargetFieldTrait
{
    /**
     * @return $this
     */
    public function setTargetField(string $targetField): self
    {
        return $this->setParam('target_field', $targetField);
    }
}
