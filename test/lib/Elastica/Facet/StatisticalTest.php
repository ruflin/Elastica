<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Facet_StatisticalTest extends Elastica_Test
{
    public function testStatisticalWithSetField()
    {
        $client = new Elastica_Client();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('helloworld');

        $doc = new Elastica_Document(1, array('price' => 10));
        $type->addDocument($doc);
        $doc = new Elastica_Document(2, array('price' => 35));
        $type->addDocument($doc);
        $doc = new Elastica_Document(2, array('price' => 45));
        $type->addDocument($doc);

        $facet = new Elastica_Facet_Statistical('stats');
        $facet->setField('price');

        $query = new Elastica_Query();
        $query->addFacet($facet);
        $query->setQuery(new Elastica_Query_MatchAll());

        $index->refresh();

        $response = $type->search($query);
        $facets = $response->getFacets();

        $this->assertEquals(55, $facets['stats']['total']);
        $this->assertEquals(10, $facets['stats']['min']);
        $this->assertEquals(45, $facets['stats']['max']);
    }

    public function testStatisticalWithSetFields()
    {
        $client = new Elastica_Client();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('helloworld');

        $doc = new Elastica_Document(1, array('price' => 10, 'price2' => 20));
        $type->addDocument($doc);
        $doc = new Elastica_Document(2, array('price' => 35, 'price2' => 70));
        $type->addDocument($doc);
        $doc = new Elastica_Document(2, array('price' => 45, 'price2' => 90));
        $type->addDocument($doc);

        $facet = new Elastica_Facet_Statistical('stats');
        $facet->setFields(array('price','price2'));

        $query = new Elastica_Query();
        $query->addFacet($facet);
        $query->setQuery(new Elastica_Query_MatchAll());

        $index->refresh();

        $response = $type->search($query);
        $facets = $response->getFacets();

        $this->assertEquals(165, $facets['stats']['total']);
        $this->assertEquals(10, $facets['stats']['min']);
        $this->assertEquals(90, $facets['stats']['max']);
    }

    public function testStatisticalWithSetScript()
    {
        $this->markTestIncomplete('Test for setting the script value');
    }
}
