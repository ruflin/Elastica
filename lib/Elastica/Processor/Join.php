<?php
namespace Elastica\Processor;

/**
 * Elastica Join Processor.
 *
 * @author   Federico Panini <fpanini@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/join-processor.html
 */
class Join extends AbstractProcessor
{
    /**
     * Join constructor.
     *
     * @param $field
     * @param $separator
     */
    public function __construct($field, $separator)
    {
        $this->setField($field);
        $this->setSeparator($separator);
    }

    /**
     * Set the field.
     *
     * @param string $field
     *
     * @return $this
     */
    public function setField(string $field)
    {
        return $this->setParam('field', $field);
    }

    /**
     * Set the separator.
     *
     * @param string $separator
     *
     * @return $this
     */
    public function setSeparator(string $separator)
    {
        return $this->setParam('separator', $separator);
    }
}
