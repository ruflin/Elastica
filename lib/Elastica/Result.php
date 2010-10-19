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
	protected $_hit;
	
	public function __construct(array $hit) {
		$this->_hit = $hit;
	}

	public function getId() {
		return $this->_hit['_id'];
	}
	
	public function getType() {
		return $this->_hit['_type'];
	}
	
	public function getScore() {
		return $this->_hit['_score'];		
	}
	
	public function getData() {
		$hit = $this->_hit;
		return $this->_hit['_source'];
	}
	
	public function __get($key) {
		$source = $this->getData();
		return array_key_exists($key, $source) ? $source[$key] : null; 
	}
}

/**
	[hits] => Array
						   (
							   [0] => Array
								   (
									   [_index] => user
									   [_type] => user
									   [_id] => 2
									   [_score] => 2.3862944
									   [_source] => Array
										   (
											   [id] => 2
											   [email] => jenny693@hotmail.com
											   [username] => jenny69
											   [sex] => 1
											   [match_sex] => 2
											   [match_age_start] => 20
											   [match_age_end] => 40
											   [headline] => Looking for fun
											   [country_id] => US
											   [state_id] => US06
											   [city_id] => 0
											   [zip] => 94111
											   [has_photo] => y
											   [on_the_site_for_] => 
											   [rating] => 3.8651
											   [rating_count] => 481
										   )

								   )

							   [1] => Array
								   (
									   [_index] => user
									   [_type] => user
									   [_id] => 22
									   [_score] => 2.3862944
									   [_source] => Array
										   (
											   [id] => 2
											   [email] => jenny693@hotmail.com
											   [username] => jenny69
											   [sex] => 1
											   [match_sex] => 2
											   [match_age_start] => 20
											   [match_age_end] => 40
											   [headline] => Looking for fun
											   [country_id] => US
											   [state_id] => US06
											   [city_id] => 0
											   [zip] => 94111
											   [has_photo] => y
											   [on_the_site_for_] => 
											   [rating] => 3.8651
											   [rating_count] => 481
										   )

								   )

						   )

*/