<?php

declare(strict_types=1);

namespace Elastica\Aggregation;

/**
 * Class IpRange.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-iprange-aggregation.html
 */
class IpRange extends AbstractAggregation
{
    use Traits\KeyedTrait;
    use Traits\RangeTrait;

    /**
     * @param string $name  the name of this aggregation
     * @param string $field the field on which to perform this aggregation
     */
    public function __construct(string $name, string $field)
    {
        parent::__construct($name);
        $this->setField($field);
    }

    /**
     * Set the field for this aggregation.
     *
     * @param string $field the name of the document field on which to perform this aggregation
     *
     * @return $this
     */
    public function setField(string $field): self
    {
        return $this->setParam('field', $field);
    }

    /**
     * Add an ip range in the form of a CIDR mask.
     *
     * @param string      $mask a valid CIDR mask
     * @param string|null $key  customized key value
     *
     * @return $this
     */
    public function addMaskRange(string $mask, ?string $key = null): self
    {
        $range = ['mask' => $mask];

        if (null !== $key) {
            $range['key'] = $key;
        }

        return $this->addParam('ranges', $range);
    }
}
