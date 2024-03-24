<?php

declare(strict_types=1);

namespace Elastica\Processor\Traits;

trait FieldTrait
{
    /**
     * @return $this
     */
    public function setField(string $field): self
    {
        return $this->setParam('field', $field);
    }
}
