<?php

/**
 * Elastica searchable interface
 *
 * @category Xodoa
 * @package Elastica
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
interface Elastica_SearchableInterface
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
	 * @param array|Elastica_Query Array with all querie data inside or a Elastica_Query object
	 * @return Elastica_ResultSet ResultSet with all results inside
	 */
    function search($query);
}
