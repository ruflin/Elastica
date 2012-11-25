<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Multi_SearchTest extends Elastica_Test
{
    public function testConstruct()
    {
        $client = new Elastica_Client();
        $multiSearch = new Elastica_Multi_Search($client);

        $this->assertInstanceOf('Elastica_Multi_Search', $multiSearch);
        $this->assertSame($client, $multiSearch->getClient());
    }

    public function testSearch()
    {
        $client = new Elastica_Client();

        $index = $client->getIndex('zero');
        $index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0)), true);

        $docs = array();
        $docs[] = new Elastica_Document(1, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(2, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(3, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(4, array('id' => 1, 'email' => 'test@test.com', 'username' => 'kate'));
        $docs[] = new Elastica_Document(5, array('id' => 1, 'email' => 'test@test.com', 'username' => 'kate'));
        $docs[] = new Elastica_Document(6, array('id' => 1, 'email' => 'test@test.com', 'username' => 'bunny'));
        $docs[] = new Elastica_Document(7, array('id' => 1, 'email' => 'test@test.com', 'username' => 'bunny'));
        $docs[] = new Elastica_Document(8, array('id' => 1, 'email' => 'test@test.com', 'username' => 'bunny'));
        $docs[] = new Elastica_Document(9, array('id' => 1, 'email' => 'test@test.com', 'username' => 'bunny'));
        $docs[] = new Elastica_Document(10, array('id' => 1, 'email' => 'test@test.com', 'username' => 'bunny'));
        $docs[] = new Elastica_Document(11, array('id' => 1, 'email' => 'test@test.com', 'username' => 'bunny'));
        $type = $index->getType('zeroType');
        $type->addDocuments($docs);
        $index->refresh();

        $multiSearch = new Elastica_Multi_Search($client);

        $search1 = new Elastica_Search($client);
        $search1->addIndex($index)->addType($type);
        $query1 = new Elastica_Query();
        $termQuery1 = new Elastica_Query_Term();
        $termQuery1->setTerm('username', 'farrelley');
        $query1->setQuery($termQuery1);
        $query1->setLimit(2);
        $search1->setQuery($query1);

        $multiSearch->addSearch($search1);

        $this->assertCount(1, $multiSearch->getSearches());

        $search2 = new Elastica_Search($client);
        $search2->addIndex($index)->addType($type);
        $query2 = new Elastica_Query();
        $termQuery2 = new Elastica_Query_Term();
        $termQuery2->setTerm('username', 'bunny');
        $query2->setQuery($termQuery2);
        $query2->setLimit(3);
        $search2->setQuery($query2);

        $multiSearch->addSearch($search2);

        $this->assertCount(2, $multiSearch->getSearches());

        $searches = $multiSearch->getSearches();
        $this->assertSame($search1, $searches[0]);
        $this->assertSame($search2, $searches[1]);

        $multiResultSet = $multiSearch->search();

        $this->assertInstanceOf('Elastica_Multi_ResultSet', $multiResultSet);
        $this->assertCount(2, $multiResultSet);
        $this->assertInstanceOf('Elastica_Response', $multiResultSet->getResponse());

        $resultSets = $multiResultSet->getResultSets();

        $this->assertInternalType('array', $resultSets);

        $this->assertArrayHasKey(0, $resultSets);
        $this->assertInstanceOf('Elastica_ResultSet', $resultSets[0]);
        $this->assertCount(2, $resultSets[0]);
        $this->assertSame($query1, $resultSets[0]->getQuery());
        $this->assertEquals(3, $resultSets[0]->getTotalHits());

        $this->assertArrayHasKey(1, $resultSets);
        $this->assertInstanceOf('Elastica_ResultSet', $resultSets[1]);
        $this->assertCount(3, $resultSets[1]);
        $this->assertSame($query2, $resultSets[1]->getQuery());
        $this->assertEquals(6, $resultSets[1]->getTotalHits());


        $search1->setOption(Elastica_Search::OPTION_SEARCH_TYPE, Elastica_Search::OPTION_SEARCH_TYPE_COUNT);
        $search2->setOption(Elastica_Search::OPTION_SEARCH_TYPE, Elastica_Search::OPTION_SEARCH_TYPE_COUNT);

        $multiResultSet = $multiSearch->search();

        $this->assertInstanceOf('Elastica_Multi_ResultSet', $multiResultSet);
        $this->assertCount(2, $multiResultSet);
        $this->assertInstanceOf('Elastica_Response', $multiResultSet->getResponse());

        $resultSets = $multiResultSet->getResultSets();

        $this->assertInternalType('array', $resultSets);

        $this->assertArrayHasKey(0, $resultSets);
        $this->assertInstanceOf('Elastica_ResultSet', $resultSets[0]);
        $this->assertCount(0, $resultSets[0]);
        $this->assertSame($query1, $resultSets[0]->getQuery());
        $this->assertEquals(3, $resultSets[0]->getTotalHits());

        $this->assertArrayHasKey(1, $resultSets);
        $this->assertInstanceOf('Elastica_ResultSet', $resultSets[1]);
        $this->assertCount(0, $resultSets[1]);
        $this->assertSame($query2, $resultSets[1]->getQuery());
        $this->assertEquals(6, $resultSets[1]->getTotalHits());
    }
}
