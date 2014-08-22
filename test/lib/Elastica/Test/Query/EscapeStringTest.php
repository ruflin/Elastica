<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Index;
use Elastica\Type;
use Elastica\Util;
use Elastica\Query\QueryString;
use Elastica\Test\Base as BaseTest;

class EscapeStringTest extends BaseTest
{
    public function testSearch()
    {
        $client = $this->_getClient();
        $index = new Index($client, 'test');
        $index->create(array(), true);
        $index->getSettings()->setNumberOfReplicas(0);
        //$index->getSettings()->setNumberOfShards(1);

        $type = new Type($index, 'helloworld');

        $doc = new Document(1, array(
            'email' => 'test@test.com', 'username' => 'test 7/6 123', 'test' => array('2', '3', '5'))
        );
        $type->addDocument($doc);

        // Refresh index
        $index->refresh();

        $queryString = new QueryString( Util::escapeTerm('test 7/6') );
        $resultSet = $type->search($queryString);

        $this->assertEquals(1, $resultSet->count());
    }
}
