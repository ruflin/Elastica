<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use Elastica\Query\MultiMatch;
use Elastica\Type;
use Elastica\Test\Base as BaseTest;

class MultiMatchTest extends BaseTest
{
   public function testQuery()
    {
        $client = $this->_getClient();
        $index = new Index($client, 'test');
        $index->create(array(), true);

        $type = new Type($index, 'multi_match');

        $doc = new Document(1, array('id' => 1, 'name' => 'Rodolfo', 'last_name' => 'Moraes'));
        $type->addDocument($doc);

        // Refresh index
        $index->refresh();

        $multiMatch = new MultiMatch();
        $query = new Query();

        $multiMatch->setQuery('Rodolfo');
        $multiMatch->setFields(array('name', 'last_name'));
        $query->setQuery($multiMatch);
        $resultSet = $index->search($query);

        $this->assertEquals(1, $resultSet->count());

        $multiMatch->setQuery('Moraes');
        $multiMatch->setFields(array('name', 'last_name'));
        $query->setQuery($multiMatch);
        $resultSet = $index->search($query);

        $this->assertEquals(1, $resultSet->count());
    }
}
