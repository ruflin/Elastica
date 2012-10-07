<?php

/**
 * Wraps status information for an index.
 * 
 * @package Elastica
 * @author Ray Ward <ray.ward@bigcommerce.com>
 * @link http://www.elasticsearch.org/guide/reference/api/admin-cluster-health.html
 */
class Elastica_Cluster_Health_Index
{
    /**
     * The name of the index.
     *
     * @var string
     */
    protected $_name;

    /**
     * The index data.
     *
     * @var array
     */
    protected $_data;

    /**
     * @param string $name The name of the index.
     * @param array $data The index status data.
     */
    public function __construct($name, $data)
    {
        $this->_name = $name;
        $this->_data = $data;
    }
}
