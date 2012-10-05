<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Cluster_HealthTest extends Elastica_Test
{
    /**
     * @var Elastica_Cluster_Health
     */
    protected $_health;

    public function setUp()
    {
        $this->_health = new Elastica_Cluster_Health($this->_getClient());
    }

    public function testGetClusterName()
    {
        $this->assertEquals('elasticsearch', $this->_health->getClusterName());
    }

    public function testGetStatus()
    {
        $this->_assertEquals('green', $this->_health->getStatus());
    }
}
