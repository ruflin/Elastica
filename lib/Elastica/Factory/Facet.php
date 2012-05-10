<?php

class Elastica_Factory_Facet
{
	public function terms($name) {
		return new Elastica_Facet_Terms($name);
	}
}
