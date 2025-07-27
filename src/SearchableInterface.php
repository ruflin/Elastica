<?php

declare(strict_types=1);

namespace Elastica;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Elastica\Exception\ClientException;
use Elastica\Exception\InvalidException;
use Elastica\Query\AbstractQuery;
use Elastica\Suggest\AbstractSuggest;

/**
 * Elastica searchable interface.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 *
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
     * @param AbstractQuery|AbstractSuggest|array|Collapse|Query|string|Suggest|null $query   Array with all query data inside or a Elastica\Query object
     * @param array<string, mixed>|null                                              $options associative array of options (option=>value)
     *
     * @phpstan-param TCreateQueryArgs $query
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws InvalidException
     * @throws ClientException
     */
    public function search($query = '', ?array $options = null): ResultSet;

    /**
     * Counts results for a query.
     *
     * If no query is set, matchall query is created
     *
     * @param AbstractQuery|array|Query|string|null $query Array with all query data inside or a Elastica\Query object
     *
     * @phpstan-param TCreateQueryArgsMatching $query
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     *
     * @return int number of documents matching the query
     */
    public function count($query = '');

    /**
     * @param AbstractQuery|AbstractSuggest|array|Collapse|Query|string|Suggest|null $query
     * @param array<string, mixed>|null                                              $options
     *
     * @phpstan-param TCreateQueryArgs $query
     */
    public function createSearch($query = '', ?array $options = null): Search;
}
