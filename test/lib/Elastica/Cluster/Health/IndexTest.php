<?php
require_once dirname(__FILE__) . '/../../../../bootstrap.php';

class Elastica_Cluster_Health_IndexTest extends Elastica_Test
{
    /**
     * @var Elastica_Cluster_index_Index
     */
    protected $_index;

    public function setUp()
    {
        $this->_createIndex();

        $health = new Elastica_Cluster_Health($this->_getClient());
        $indices = $health->getIndices();
        $this->_index = $indices[0];
    }

    public function testGetName()
    {
        $this->assertEquals('elastica_test', $this->_index->getName());
    }

    public function testGetStatus()
    {
        $this->assertContains($this->_index->getStatus(), array('red', 'yellow', 'green'));
    }
  
    public function testGetNumberOfShards()
    {
        $this->assertInternalType('int', $this->_index->getNumberOfShards());
    }

    public function testGetNumberOfReplicas()
    {
        $this->assertInternalType('int', $this->_index->getNumberOfReplicas());
    }

    public function testGetActivePrimaryShards()
    {
        $this->assertInternalType('int', $this->_index->getActivePrimaryShards());
    }

    public function testGetActiveShards()
    {
        $this->assertInternalType('int', $this->_index->getActiveShards());
    }

    public function testGetRelocatingShards()
    {
        $this->assertInternalType('int', $this->_index->getRelocatingShards());
    }

    public function testGetInitializingShards()
    {
        $this->assertInternalType('int', $this->_index->getInitializingShards());
    }

    public function testGetUnassignedShards()
    {
        $this->assertInternalType('int', $this->_index->getUnassignedShards());
    }
}
