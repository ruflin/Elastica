<?php

namespace Elastica\Test\Exception;

use Elastica\Document;
use Elastica\Exception\PartialShardFailureException;
use Elastica\Query;
use Elastica\ResultSet;
use Elastica\Test\Base as BaseTest;

class PartialShardFailureExceptionTest extends BaseTest
{

    public function testPartialFailure()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('elastica_partial_failure');
        $index->create(array(
            'index' => array(
                'number_of_shards'   => 5,
                'number_of_replicas' => 0
            )
        ), true);

        $type = $index->getType('folks');

        $type->addDocument(new Document('', array('name' => 'ruflin')));
        $type->addDocument(new Document('', array('name' => 'bobrik')));
        $type->addDocument(new Document('', array('name' => 'kimchy')));

        $index->refresh();

        $query = Query::create(array(
            'query' => array(
                'filtered' => array(
                    'filter' => array(
                        'script' => array(
                            'script' => 'doc["undefined"] > 8', // compiles, but doesn't work
                        ),
                    ),
                ),
            ),
        ));

        try {
            $index->search($query);

            $this->fail('PartialShardFailureException should have been thrown');
        } catch (PartialShardFailureException $e) {
            $resultSet = new ResultSet($e->getResponse(), $query);
            $this->assertEquals(0, count($resultSet->getResults()));
        }
    }

}
