<?php

namespace Elastica\Query;

use Elastica\Exception\InvalidException;

/**
 * Fuzzy query.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-fuzzy-query.html
 */
class Fuzzy extends AbstractQuery
{
    /**
     * Construct a fuzzy query.
     *
     * @param string $value String to search for
     */
    public function __construct(?string $fieldName = null, ?string $value = null)
    {
        if (null !== $fieldName && null !== $value) {
            $this->setField($fieldName, $value);
        }
    }

    /**
     * Set field for fuzzy query.
     *
     * @param string $value String to search for
     *
     * @return $this
     */
    public function setField(string $fieldName, string $value): self
    {
        if (\count($this->getParams()) > 0 && \array_key_first($this->getParams()) !== $fieldName) {
            throw new InvalidException('Fuzzy query can only support a single field.');
        }

        return $this->setParam($fieldName, ['value' => $value]);
    }

    /**
     * Set optional parameters on the existing query.
     *
     * @param mixed $value Value of the parameter
     *
     * @return $this
     */
    public function setFieldOption(string $option, $value): self
    {
        //Retrieve the single existing field for alteration.
        if (null === $key = \array_key_first($params = $this->getParams())) {
            throw new InvalidException('No field has been set');
        }

        $params[$key][$option] = $value;

        return $this->setParam($key, $params[$key]);
    }
}
