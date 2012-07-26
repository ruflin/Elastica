<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Facet_QueryTest extends PHPUnit_Framework_TestCase
{
    public function testFilter()
    {
        $client = new Elastica_Client();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('helloworld');

        $type->addDocument(new Elastica_Document(1, array('color' => 'red')));
        $type->addDocument(new Elastica_Document(2, array('color' => 'green')));
        $type->addDocument(new Elastica_Document(3, array('color' => 'blue')));

        $index->refresh();

        $termQuery = new Elastica_Query_Term(array('color' => 'red'));

        $facet = new Elastica_Facet_Query('test');
        $facet->setQuery($termQuery);

        $query = new Elastica_Query();
        $query->addFacet($facet);

        $resultSet = $type->search($query);

        $facets = $resultSet->getFacets();

        $this->assertEquals(1, $facets['test']['count']);
    }
}
