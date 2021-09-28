<?php

namespace Elastica;

use Elastica\Query\AbstractQuery;

/**
 * Elastica searchable interface.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
interface SearchableInterface
{
    /**
     * Searches results for a query.
     *
     * {
     *     "from" : 0,
     *     "size" : 10,
     *     "sort" : {
     *          "postDate" : {"order" : "desc"},
     *          "user" : { },
     *          "_score" : { }
     *      },
     *      "query" : {
     *          "term" : { "user" : "kimchy" }
     *      }
     * }
     *
     * @param AbstractQuery|array|Collapse|Query|string|Suggest $query   Array with all query data inside or a Elastica\Query object
     * @param array|int                                         $options Limit or associative array of options (option=>value)
     * @param string                                            $method  Request method, see Request's constants
     */
    public function search($query = '', $options = null, string $method = Request::POST): ResultSet;

    /**
     * Counts results for a query.
     *
     * If no query is set, matchall query is created
     *
     * @param AbstractQuery|array|Collapse|Query|string|Suggest $query  Array with all query data inside or a Elastica\Query object
     * @param string                                            $method Request method, see Request's constants
     *
     * @return int number of documents matching the query
     */
    public function count($query = '', string $method = Request::POST);

    /**
     * @param AbstractQuery|array|Collapse|Query|string|Suggest $query
     * @param mixed|null                                        $options
     */
    public function createSearch($query = '', $options = null): Search;
}
