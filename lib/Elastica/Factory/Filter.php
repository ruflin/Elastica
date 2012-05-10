<?php

class Elastica_Factory_Filter
{
	public function geo($key, $topLeftLatitude, $topLeftLongitude, $bottomRightLatitude, $bottomRightLongitude) {
		return new Elastica_Filter_GeoBoundingBox($key, $topLeftLatitude, $topLeftLongitude, $bottomRightLatitude, $bottomRightLongitude);
	}

	public function and_() {
		return new Elastica_Filter_And();
	}

	public function terms($key = '', array $terms = array()) {
		return new Elastica_Filter_Terms($key, $terms);
	}
}
