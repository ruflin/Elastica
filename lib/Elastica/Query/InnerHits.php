<?php

namespace Elastica\Query;

use Elastica\Script\AbstractScript;
use Elastica\Script\ScriptFields;

/**
 * Nested query.
 *
 * @author Guillaume Affringue <wamania@yahoo.fr>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-inner-hits.html
 */
class InnerHits extends AbstractQuery
{
    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $array = parent::toArray();

        // if there are no params, it's ok, but ES will throw exception if json
        // will be like {"top_hits":[]} instead of {"top_hits":{}}
        if (empty($array['inner_hits'])) {
            $array['inner_hits'] = new \stdClass();
        }

        return $array['inner_hits'];
    }

    /**
     * The name to be used for the particular inner hit definition in the response.
     * Useful when multiple inner hits have been defined in a single search request.
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        return $this->setParam('name', $name);
    }

    /**
     * The maximum number of inner matching hits to return per bucket. By default the top three matching hits are returned.
     *
     * @return $this
     */
    public function setSize(int $size = 3): self
    {
        return $this->setParam('size', $size);
    }

    /**
     * The offset from the first result you want to fetch.
     *
     * @return $this
     */
    public function setFrom(int $from)
    {
        return $this->setParam('from', $from);
    }

    /**
     * How the inner matching hits should be sorted. By default the hits are sorted by the score of the main query.
     *
     * @return $this
     */
    public function setSort(array $sortArgs): self
    {
        return $this->setParam('sort', $sortArgs);
    }

    /**
     * Allows to control how the _source field is returned with every hit.
     *
     * @param array|bool $params Fields to be returned or false to disable source
     *
     * @return $this
     */
    public function setSource($params): self
    {
        return $this->setParam('_source', $params);
    }

    /**
     * Returns a version for each search hit.
     *
     * @return $this
     */
    public function setVersion(bool $version): self
    {
        return $this->setParam('version', $version);
    }

    /**
     * Enables explanation for each hit on how its score was computed.
     *
     * @return $this
     */
    public function setExplain(bool $explain): self
    {
        return $this->setParam('explain', $explain);
    }

    /**
     * Set script fields.
     *
     * @return $this
     */
    public function setScriptFields(ScriptFields $scriptFields): self
    {
        return $this->setParam('script_fields', $scriptFields);
    }

    /**
     * Adds a Script to the aggregation.
     *
     * @return $this
     */
    public function addScriptField(string $name, AbstractScript $script): self
    {
        if (!isset($this->_params['script_fields'])) {
            $this->_params['script_fields'] = new ScriptFields();
        }

        $this->_params['script_fields']->addScript($name, $script);

        return $this;
    }

    /**
     * Sets highlight arguments for the results.
     *
     * @return $this
     */
    public function setHighlight(array $highlightArgs): self
    {
        return $this->setParam('highlight', $highlightArgs);
    }

    /**
     * Allows to return the field data representation of a field for each hit.
     *
     * @return $this
     */
    public function setFieldDataFields(array $fields): self
    {
        return $this->setParam('docvalue_fields', $fields);
    }
}
