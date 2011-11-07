<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Index_StatsTest extends PHPUnit_Framework_TestCase
{

	public function testGetSettings() {
		$indexName = 'test';

		$client = new Elastica_Client();
		$index = $client->getIndex($indexName);
		$index->create(array(), true);
		$stats = $index->getStats();
		$this->assertInstanceOf('Elastica_Index_Stats', $stats);
		
		$this->assertTrue($stats->get('ok'));
		$this->assertEquals(0, $stats->get('_all', 'indices', 'test', 'primaries', 'docs', 'count'));
	}
}