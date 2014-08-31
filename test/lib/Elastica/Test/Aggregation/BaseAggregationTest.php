<?php

namespace Elastica\Test\Aggregation;


use Elastica\Index;
use Elastica\Test\Base;

abstract class BaseAggregationTest extends Base
{
    /**
     * @var Index
     */
    protected $_index;

    protected function tearDown()
    {
        parent::tearDown();
        if ($this->_index instanceof Index) {
            $this->_index->delete();
        }
    }

    protected function _createIndex($name = 'test', $delete = true, $shards = 1)
    {
        return parent::_createIndex('test_aggregation_' . $name, $delete, $shards);
    }
}