<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Cluster_HealthTest extends Elastica_Test
{
    public function testGetClusterName()
    {
        $health = new Elastica_Cluster_Health($this->_getClient());
        $this->assertEquals('elasticsearch', $health->getClusterName());
    }
}
