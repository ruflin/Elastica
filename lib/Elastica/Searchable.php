<?php

/**
 * Elastica searchable interface
 *
 * @category Xodoa
 * @package Elastica
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
interface Elastica_Searchable
{
	/**
	 * Searches results for a query
	 *
	 * TODO: Improve sample code
	 * {
	 *	 "from" : 0,
	 *	 "size" : 10,
	 *	 "sort" : {
	 *		  "postDate" : {"reverse" : true},
	 *		  "user" : { },
	 *		  "_score" : { }
	 *	  },
	 *	  "query" : {
	 *		  "term" : { "user" : "kimchy" }
	 *	  }
	 * }
	 *
	 * @param string|array|Elastica_Query $query Array with all query data inside or a Elastica_Query object
	 * @return Elastica_ResultSet ResultSet with all results inside
	 */
	public function search($query);

	/**
	 * Counts results for a query
	 *
	 * If no query is set, matchall query is created
	 *
	 * @param string|array|Elastica_Query $query Array with all query data inside or a Elastica_Query object
	 * @return int number of documents matching the query
	 */
	public function count($query = '');
}
