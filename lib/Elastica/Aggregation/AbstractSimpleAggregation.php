<?php
namespace Elastica\Aggregation;

use Elastica\Script;

abstract class AbstractSimpleAggregation extends AbstractAggregation
{
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
     * Set a script for this aggregation.
     *
     * @param string|Script $script
     *
     * @return $this
     */
    public function setScript($script)
    {
        if ($script instanceof Script) {
            $params = array_merge($this->getParams(), $script->toArray());

            return $this->setParams($params);
        }

        return $this->setParam('script', $script);
    }
}
