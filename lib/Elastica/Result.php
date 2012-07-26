<?php
/**
 * Elastica result item
 *
 * Stores all information from a result
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Result
{
    /**
     * Hit array
     *
     * @var array Hit array
     */
    protected $_hit = array();

    /**
     * Constructs a single results object
     *
     * @param array $hit Hit data
     */
    public function __construct(array $hit)
    {
        $this->_hit = $hit;
    }

    /**
     * Returns a param from the result hit array
     *
     * This function can be used to retrieve all data for which a specific
     * function doesn't exist.
     * If the param does not exist, an empty array is retured
     *
     * @param  string $name Param name
     * @return array  Result data
     */
    public function getParam($name)
    {
        if (isset($this->_hit[$name])) {
            return $this->_hit[$name];
        } else {
            return array();
        }
    }

    /**
     * Returns the hit id
     *
     * @return string Hit id
     */
    public function getId()
    {
        return $this->getParam('_id');
    }

    /**
     * Returns the type of the result
     *
     * @return string Result type
     */
    public function getType()
    {
        return $this->getParam('_type');
    }

    /**
     * Returns list of fields
     *
     * @return array Fields list
     */
    public function getFields()
    {
        return $this->getParam('fields');
    }

    /**
     * Returns the index name of the result
     *
     * @return string Index name
     */
    public function getIndex()
    {
        return $this->getParam('_index');
    }

    /**
     * Returns the score of the result
     *
     * @return float Result score
     */
    public function getScore()
    {
        return $this->getParam('_score');
    }

    /**
     * Returns the raw hit array
     *
     * @return array Hit array
     */
    public function getHit()
    {
        return $this->_hit;
    }

    /**
     * Returns the version information from the hit
     *
     * @return string|int Document version
     */
    public function getVersion()
    {
        return $this->getParam('_version');
    }

    /**
     * Returns result data
     *
     * Checks for partial result data with getFields, falls back to getSource
     *
     * @return array Result data array
     */
    public function getData()
    {
        if (isset($this->_hit['fields']) && !isset($this->_hit['_source'])) {
            return $this->getFields();
        } else {
            return $this->getSource();
        }
    }

    /**
     * Returns the result source
     *
     * @return array Source data array
     */
    public function getSource()
    {
        return $this->getParam('_source');
    }

    /**
     * Returns result data
     *
     * @return array Result data array
     */
    public function getHighlights()
    {
        return $this->getParam('highlight');
    }

    /**
     * Returns explanation on how its score was computed.
     *
     * @return array explanations
     */
    public function getExplanation()
    {
        return $this->getParam('_explanation');
    }

    /**
     * Magic function to directly access keys inside the result
     *
     * Returns null if key does not exist
     *
     * @param  string $key Key name
     * @return mixed  Key value
     */
    public function __get($key)
    {
        $source = $this->getData();

        return array_key_exists($key, $source) ? $source[$key] : null;
    }
}
