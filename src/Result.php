<?php

namespace Elastica;

/**
 * Elastica result item.
 *
 * Stores all information from a result
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Result
{
    /**
     * Hit array.
     *
     * @var array Hit array
     */
    protected $_hit = [];

    /**
     * @param array $hit Hit data
     */
    public function __construct(array $hit)
    {
        $this->_hit = $hit;
    }

    /**
     * Magic function to directly access keys inside the result.
     *
     * Returns null if key does not exist
     *
     * @param string $key Key name
     *
     * @return mixed Key value
     */
    public function __get($key)
    {
        $source = $this->getData();

        return $source[$key] ?? null;
    }

    /**
     * Magic function to support isset() calls.
     *
     * @param string $key Key name
     */
    public function __isset($key): bool
    {
        $source = $this->getData();

        return \array_key_exists($key, $source) && null !== $source[$key];
    }

    /**
     * Returns a param from the result hit array.
     *
     * This function can be used to retrieve all data for which a specific
     * function doesn't exist.
     * If the param does not exist, an empty array is returned
     *
     * @param string $name Param name
     *
     * @return mixed Result data
     */
    public function getParam($name)
    {
        return $this->_hit[$name] ?? [];
    }

    /**
     * Test if a param from the result hit is set.
     */
    public function hasParam(string $name): bool
    {
        return isset($this->_hit[$name]);
    }

    /**
     * Returns the hit id.
     *
     * @return string Hit id
     */
    public function getId()
    {
        return $this->getParam('_id');
    }

    /**
     * Returns the type of the result.
     *
     * @return string Result type
     *
     * @deprecated ES 7.x deprecated the use of types in the index
     */
    public function getType()
    {
        return $this->getParam('_type');
    }

    /**
     * Returns list of fields.
     */
    public function getFields(): array
    {
        return $this->getParam('fields');
    }

    /**
     * Returns whether result has fields.
     */
    public function hasFields(): bool
    {
        return $this->hasParam('fields');
    }

    /**
     * Returns the index name of the result.
     *
     * @return string Index name
     */
    public function getIndex()
    {
        return $this->getParam('_index');
    }

    /**
     * Returns the score of the result.
     *
     * @return float Result score
     */
    public function getScore()
    {
        return $this->getParam('_score');
    }

    /**
     * Returns the raw hit array.
     */
    public function getHit(): array
    {
        return $this->_hit;
    }

    /**
     * Returns the version information from the hit.
     *
     * @return int|string Document version
     */
    public function getVersion()
    {
        return $this->getParam('_version');
    }

    /**
     * Returns inner hits.
     */
    public function getInnerHits(): array
    {
        return $this->getParam('inner_hits');
    }

    /**
     * Returns whether result has inner hits.
     */
    public function hasInnerHits(): bool
    {
        return $this->hasParam('inner_hits');
    }

    /**
     * Returns result data.
     *
     * Checks for partial result data with getFields, falls back to getSource or both
     *
     * @return array Result data array
     */
    public function getData()
    {
        if (isset($this->_hit['fields'])) {
            return isset($this->_hit['_source'])
                ? \array_merge($this->getFields(), $this->getSource())
                : $this->getFields();
        }

        return $this->getSource();
    }

    /**
     * Returns the result source.
     */
    public function getSource(): array
    {
        return $this->getParam('_source');
    }

    /**
     * Returns result data.
     */
    public function getHighlights(): array
    {
        return $this->getParam('highlight');
    }

    /**
     * Returns explanation on how its score was computed.
     */
    public function getExplanation(): array
    {
        return $this->getParam('_explanation');
    }

    /**
     * Returns Document.
     */
    public function getDocument(): Document
    {
        $doc = new Document();
        $doc->setData($this->getSource());
        $hit = $this->getHit();
        unset($hit['_source'], $hit['_explanation'], $hit['highlight'], $hit['_score']);

        $doc->setParams($hit);

        return $doc;
    }

    /**
     * Sets a parameter on the hit.
     *
     * @param mixed $value
     */
    public function setParam(string $param, $value): void
    {
        $this->_hit[$param] = $value;
    }
}
