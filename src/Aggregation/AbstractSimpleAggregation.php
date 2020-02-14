<?php

namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;

abstract class AbstractSimpleAggregation extends AbstractAggregation
{
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
     * Set a script for this aggregation.
     *
     * @param \Elastica\Script\AbstractScript|string $script
     *
     * @return $this
     */
    public function setScript($script): self
    {
        return $this->setParam('script', $script);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        if (!$this->hasParam('field') && !$this->hasParam('script')) {
            throw new InvalidException('Either the field param or the script param should be set');
        }
        $array = parent::toArray();

        $baseName = $this->_getBaseName();

        if (isset($array[$baseName]['script']) && \is_array($array[$baseName]['script'])) {
            $script = $array[$baseName]['script'];

            unset($array[$baseName]['script']);

            $array[$baseName] = \array_merge($array[$baseName], $script);
        }

        return $array;
    }
}
