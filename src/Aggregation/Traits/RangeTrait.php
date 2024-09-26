<?php

declare(strict_types=1);

namespace Elastica\Aggregation\Traits;

use Elastica\Exception\InvalidException;

trait RangeTrait
{
    /**
     * Add a range to this aggregation.
     *
     * @param float|int|string|null $fromValue low end of this range, exclusive (greater than or equal to)
     * @param float|int|string|null $toValue   high end of this range, exclusive (less than)
     * @param string|null           $key       customized key value
     *
     * @throws InvalidException
     *
     * @return $this
     */
    public function addRange($fromValue = null, $toValue = null, ?string $key = null): self
    {
        if (null === $fromValue && null === $toValue) {
            throw new InvalidException('Either fromValue or toValue must be set. Both cannot be null.');
        }

        $range = [];

        if (null !== $fromValue) {
            $range['from'] = $fromValue;
        }

        if (null !== $toValue) {
            $range['to'] = $toValue;
        }

        if (null !== $key) {
            $range['key'] = $key;
        }

        return $this->addParam('ranges', $range);
    }
}
