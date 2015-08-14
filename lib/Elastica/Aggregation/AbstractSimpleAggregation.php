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
        return $this->setParam('script', $script);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $array = parent::toArray();

        if (isset($array[$this->_getBaseName()]['script']) && is_array($array[$this->_getBaseName()]['script'])) {
            $script = $array[$this->_getBaseName()]['script'];

            unset($array[$this->_getBaseName()]['script']);

            $array[$this->_getBaseName()] = array_merge($array[$this->_getBaseName()], $script);
        }

        return $array;
    }
}
