<?php
namespace Elastica\Filter;

use Elastica\Exception\InvalidException;
use Elastica\Query\AbstractQuery;

/**
 * Query filter.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-query-filter.html
 */
class Query extends AbstractFilter
{
    /**
     * Query.
     *
     * @var array
     */
    protected $_query;

    /**
     * Construct query filter.
     *
     * @param array|\Elastica\Query\AbstractQuery $query
     */
    public function __construct($query = null)
    {
        if (!is_null($query)) {
            $this->setQuery($query);
        }
    }

    /**
     * Set query.
     *
     * @param array|\Elastica\Query\AbstractQuery $query
     *
     * @throws \Elastica\Exception\InvalidException If parameter is invalid
     *
     * @return $this
     */
    public function setQuery($query)
    {
        if (!$query instanceof AbstractQuery && !is_array($query)) {
            throw new InvalidException('expected an array or instance of Elastica\Query\AbstractQuery');
        }

        if ($query instanceof AbstractQuery) {
            $query = $query->toArray();
        }

        $this->_query = $query;

        return $this;
    }

    /**
     * @see \Elastica\Param::_getBaseName()
     */
    protected function _getBaseName()
    {
        if (empty($this->_params)) {
            return 'query';
        } else {
            return 'fquery';
        }
    }

    /**
     * @see \Elastica\Param::toArray()
     */
    public function toArray()
    {
        $data = parent::toArray();

        $name = $this->_getBaseName();
        $filterData = $data[$name];

        if (empty($filterData)) {
            $filterData = $this->_query;
        } else {
            $filterData['query'] = $this->_query;
        }

        $data[$name] = $filterData;

        return $data;
    }
}
