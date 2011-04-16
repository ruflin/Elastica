<?php
/**
 * Geo bounding box filter
 *
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/geo_bounding_box_filter/
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Filter_GoeBoundingBox extends Elastica_Filter_Abstract
{
	public function toArray()
	{
	}
}


/**
	"filter" : {
		"geo_bounding_box" : {
			"pin.location" : {
				"top_left" : {
					"lat" : 40.73,
					"lon" : -74.1
				},
				"bottom_right" : {
					"lat" : 40.717,
					"lon" : -73.99
				}
			}
		}
	}
}
*/
