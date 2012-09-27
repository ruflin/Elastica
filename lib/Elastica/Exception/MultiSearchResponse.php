<?php
/**
 * Bulk Response exception
 *
 * @category Xodoa
 * @package Elastica
 */
class Elastica_Exception_MultiSearchResponse extends Elastica_Exception_Abstract
{
	/**
	 * Responses from the multi-search.
	 *
	 * @var array Array of Elastica_Response objects
	 */
	protected $_responses = null;

	/**
	 * Construct Exception
	 *
	 * @param array $responses The multi-search responses.
	 */
	public function __construct($responses)
	{
		$this->_responses = $responses;
		parent::__construct('Error in one or more multi search responses');
	}

	/**
	 * Gets the multi-search responses.
	 *
	 * @return array Array of Elastica_Response objects.
	 */
	public function getResponses()
	{
		return $this->_responses;
	}

	/**
	 * Returns array of errors for failed searches.
	 *
	 * @return array Array of errors. Each error has the elements 'query_index' which is the index of the query that
     * generated this error originally, and 'error', which is the response error.
	 */
	public function getFailures()
	{
		$errors = array();

		foreach ($this->_responses as $index => $response) {
           if ($response->hasError()) {
               $errors[] = array(
                   'query_index' => $index,
                   'error' => $response->getError(),
               );
           }
		}

		return $errors;
	}
}
