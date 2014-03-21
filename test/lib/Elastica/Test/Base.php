<?php

namespace Elastica\Test;

use Elastica\Client;

class Base extends \PHPUnit_Framework_TestCase
{
    protected function _getClient()
    {
        return new Client();
    }

    /**
     * @param  string         $name Index name
     * @param  bool           $delete Delete index if it exists
     * @param  int            $shards Number of shards to create
     * @return \Elastica\Index
     */
    protected function _createIndex($name = 'test', $delete = true, $shards = 1)
    {
        $client = $this->_getClient();
        $index = $client->getIndex('elastica_' . $name);
        $index->create(array('index' => array('number_of_shards' => $shards, 'number_of_replicas' => 0)), $delete);

        return $index;
    }
}
