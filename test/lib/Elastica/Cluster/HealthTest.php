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
        $this->assertEquals('green', $this->_health->getStatus());
    }

    public function testGetTimedOut()
    {
        $this->assertFalse($this->_health->getTimedOut());
    }

    public function testGetNumberOfNodes()
    {
        $this->assertInternalType('int', $this->_health->getNumberOfNodes());
    }

    public function testGetNumberOfDataNodes()
    {
        $this->assertInternalType('int', $this->_health->getNumberOfDataNodes());
    }

    public function testGetActivePrimaryShards()
    {
        $this->assertInternalType('int', $this->_health->getActivePrimaryShards());
    }

    public function testGetActiveShards()
    {
        $this->assertInternalType('int', $this->_health->getActiveShards());
    }

    public function testGetRelocatingShards()
    {
        $this->assertInternalType('int', $this->_health->getRelocatingShards());
    }

    public function testGetInitializingShards()
    {
        $this->assertInternalType('int', $this->_health->getInitializingShards());
    }

    public function testGetUnassignedShards()
    {
        $this->assertInternalType('int', $this->_health->getUnassignedShards());
    }
}
