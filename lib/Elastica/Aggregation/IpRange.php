<?php
namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;

/**
 * Class IpRange.
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-iprange-aggregation.html
 */
class IpRange extends AbstractAggregation
{
    /**
     * @param string $name  the name of this aggregation
     * @param string $field the field on which to perform this aggregation
     */
    public function __construct($name, $field)
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
    public function setField($field)
    {
        return $this->setParam('field', $field);
    }

    /**
     * Add an ip range to this aggregation.
     *
     * @param string $fromValue a valid ipv4 address. Low end of this range, exclusive (greater than)
     * @param string $toValue   a valid ipv4 address. High end of this range, exclusive (less than)
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return $this
     */
    public function addRange($fromValue = null, $toValue = null)
    {
        if (is_null($fromValue) && is_null($toValue)) {
            throw new InvalidException('Either fromValue or toValue must be set. Both cannot be null.');
        }
        $range = array();
        if (!is_null($fromValue)) {
            $range['from'] = $fromValue;
        }
        if (!is_null($toValue)) {
            $range['to'] = $toValue;
        }

        return $this->addParam('ranges', $range);
    }

    /**
     * Add an ip range in the form of a CIDR mask.
     *
     * @param string $mask a valid CIDR mask
     *
     * @return $this
     */
    public function addMaskRange($mask)
    {
        return $this->addParam('ranges', array('mask' => $mask));
    }
}
