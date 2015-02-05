<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Index;

class Base extends \PHPUnit_Framework_TestCase
{
    protected function _getClient()
    {
        return new Client(array(
            'host' => getenv('ES_HOST') ?: 'localhost',
            'port' => getenv('ES_PORT') ?: 9200,
        ));
    }

    /**
     * @param  string          $name   Index name
     * @param  bool            $delete Delete index if it exists
     * @param  int             $shards Number of shards to create
     * @return \Elastica\Index
     */
    protected function _createIndex($name = null, $delete = true, $shards = 1)
    {
        if (is_null($name)) {
            $name = preg_replace('/[^a-z]/i', '', strtolower(get_called_class())).uniqid();
        }

        $client = $this->_getClient();
        $index = $client->getIndex('elastica_'.$name);
        $index->create(array('index' => array('number_of_shards' => $shards, 'number_of_replicas' => 0)), $delete);

        return $index;
    }

    protected function _waitForAllocation(Index $index)
    {
        do {
            $settings = $index->getStatus()->get();
            $allocated = true;
            foreach ($settings['shards'] as $shard) {
                if ($shard[0]['routing']['state'] != 'STARTED') {
                    $allocated = false;
                }
            }
        } while (!$allocated);
    }

    protected function tearDown()
    {
        $this->_getClient()->getIndex('_all')->delete();
        $this->_getClient()->getIndex('_all')->clearCache();
    }
}
