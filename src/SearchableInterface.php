<?php

namespace Elastica;

use Elastica\Exception\ClientException;
use Elastica\Exception\ConnectionException;
use Elastica\Exception\InvalidException;
use Elastica\Exception\ResponseException;
use Elastica\Query\AbstractQuery;
use Elastica\Suggest\AbstractSuggest;

/**
 * Elastica searchable interface.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @phpstan-import-type TCreateQueryArgs from Query
 * @phpstan-import-type TCreateQueryArgsMatching from Query
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
     * @param AbstractQuery|AbstractSuggest|array|Collapse|Query|string|Suggest|null $query Array with all query data inside or a Elastica\Query object
     * @phpstan-param TCreateQueryArgs $query
     *
     * @param array|int|null $options Limit or associative array of options (option=>value)
     * @param string         $method  Request method, see Request's constants
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws InvalidException
     * @throws ResponseException
     */
    public function search($query = '', $options = null, string $method = Request::POST): ResultSet;

    /**
     * Counts results for a query.
     *
     * If no query is set, matchall query is created
     *
     * @param AbstractQuery|array|Query|string|null $query Array with all query data inside or a Elastica\Query object
     * @phpstan-param TCreateQueryArgsMatching $query
     *
     * @param string $method Request method, see Request's constants
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     *
     * @return int number of documents matching the query
     */
    public function count($query = '', string $method = Request::POST);

    /**
     * @param AbstractQuery|AbstractSuggest|array|Collapse|Query|string|Suggest|null $query
     * @phpstan-param TCreateQueryArgs $query
     *
     * @param array|int|null $options
     */
    public function createSearch($query = '', $options = null): Search;
}
