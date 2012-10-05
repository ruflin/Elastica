<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_MultiMatchTest extends Elastica_Test
{
   public function testQuery()
    {
        $client = new Elastica_Client();
        $index = new Elastica_Index($client, 'test');
        $index->create(array(), true);

        $type = new Elastica_Type($index, 'multi_match');

        $doc = new Elastica_Document(1, array('id' => 1, 'name' => 'Rodolfo', 'last_name' => 'Moraes'));
        $type->addDocument($doc);

        // Refresh index
        $index->refresh();

        $multiMatch = new Elastica_Query_MultiMatch();
        $query = new Elastica_Query();

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
