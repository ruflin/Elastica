<?php

namespace Elastica;

use Elastica\Exception\InvalidException;

/**
 * Class to handle params.
 *
 * This function can be used to handle params for queries, filter
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Param implements ArrayableInterface, \Countable
{
    /**
     * Params.
     *
     * @var array<string, mixed>|\stdClass
     */
    protected $_params = [];

    /**
     * Raw Params.
     *
     * @var array<string, mixed>
     */
    protected $_rawParams = [];

    /**
     * Converts the params to an array. A default implementation exist to create
     * the an array out of the class name (last part of the class name)
     * and the params.
     *
     * @return array<string, mixed> Filter array
     */
    public function toArray()
    {
        $data = [$this->_getBaseName() => $this->getParams()];

        if ($this->_rawParams) {
            $data = \array_merge($data, $this->_rawParams);
        }

        return $this->_convertArrayable($data);
    }

    /**
     * Sets (overwrites) the value at the given key.
     *
     * @param string $key   Key to set
     * @param mixed  $value Key Value
     *
     * @return $this
     */
    public function setParam($key, $value)
    {
        $this->_params[$key] = $value;

        return $this;
    }

    /**
     * Sets (overwrites) all params of this object.
     *
     * @param array $params Parameter list
     *
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->_params = $params;

        return $this;
    }

    /**
     * Adds a param to the list.
     *
     * This function can be used to add an array of params
     *
     * @param string $key   Param key
     * @param mixed  $value Value to set
     *
     * @return $this
     */
    public function addParam($key, $value, ?string $subKey = null)
    {
        if (null !== $subKey) {
            $this->_params[$key][$subKey] = $value;
        } else {
            $this->_params[$key][] = $value;
        }

        return $this;
    }

    /**
     * Returns a specific param.
     *
     * @param string $key Key to return
     *
     * @throws InvalidException If requested key is not set
     *
     * @return mixed Key value
     */
    public function getParam($key)
    {
        if (!$this->hasParam($key)) {
            throw new InvalidException('Param '.$key.' does not exist');
        }

        return $this->_params[$key];
    }

    /**
     * Test if a param is set.
     *
     * @param string $key Key to test
     *
     * @return bool True if the param is set, false otherwise
     */
    public function hasParam($key)
    {
        return isset($this->_params[$key]);
    }

    /**
     * Returns the params array.
     *
     * @return array Params
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return \count($this->_params);
    }

    /**
     * Cast objects to arrays.
     *
     * @return array
     */
    protected function _convertArrayable(array $array)
    {
        $arr = [];

        foreach ($array as $key => $value) {
            if ($value instanceof ArrayableInterface) {
                $arr[$value instanceof NameableInterface ? $value->getName() : $key] = $value->toArray();
            } elseif (\is_array($value)) {
                $arr[$key] = $this->_convertArrayable($value);
            } else {
                $arr[$key] = $value;
            }
        }

        return $arr;
    }

    /**
     * Param's name
     * Picks the last part of the class name and makes it snake_case
     * You can override this method if you want to change the name.
     *
     * @return string name
     */
    protected function _getBaseName()
    {
        return Util::toSnakeCase((new \ReflectionClass($this))->getShortName());
    }

    /**
     * Sets params not inside params array.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    protected function _setRawParam($key, $value)
    {
        $this->_rawParams[$key] = $value;

        return $this;
    }
}
