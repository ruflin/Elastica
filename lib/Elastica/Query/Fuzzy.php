<?php
namespace Elastica\Query;

use Elastica\Exception\InvalidException;

/**
 * Fuzzy query.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-fuzzy-query.html
 */
class Fuzzy extends AbstractQuery
{
    /**
     * Construct a fuzzy query.
     *
     * @param string $fieldName Field name
     * @param string $value     String to search for
     */
    public function __construct($fieldName = null, $value = null)
    {
        if ($fieldName && $value) {
            $this->setField($fieldName, $value);
        }
    }

    /**
     * Set field for fuzzy query.
     *
     * @param string $fieldName Field name
     * @param string $value     String to search for
     *
     * @return $this
     */
    public function setField($fieldName, $value)
    {
        if (!is_string($value) || !is_string($fieldName)) {
            throw new InvalidException('The field and value arguments must be of type string.');
        }
        if (count($this->getParams()) > 0 && key($this->getParams()) !== $fieldName) {
            throw new InvalidException('Fuzzy query can only support a single field.');
        }

        return $this->setParam($fieldName, ['value' => $value]);
    }

    /**
     * Set optional parameters on the existing query.
     *
     * @param string $option option name
     * @param mixed  $value  Value of the parameter
     *
     * @return $this
     */
    public function setFieldOption($option, $value)
    {
        //Retrieve the single existing field for alteration.
        $params = $this->getParams();
        if (count($params) < 1) {
            throw new InvalidException('No field has been set');
        }
        $key = key($params);
        $params[$key][$option] = $value;

        return $this->setParam($key, $params[$key]);
    }
}
