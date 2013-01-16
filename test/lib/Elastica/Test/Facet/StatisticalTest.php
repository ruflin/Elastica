<?php

namespace Elastica\Test\Facet;

use Elastica\Document;
use Elastica\Facet\Statistical;
use Elastica\Query;
use Elastica\Query\MatchAll;
use Elastica\Test\Base as BaseTest;

class StatisticalTest extends BaseTest
{
    public function testStatisticalWithSetField()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('helloworld');

        $doc = new Document(1, array('price' => 10));
        $type->addDocument($doc);
        $doc = new Document(2, array('price' => 35));
        $type->addDocument($doc);
        $doc = new Document(2, array('price' => 45));
        $type->addDocument($doc);

        $facet = new Statistical('stats');
        $facet->setField('price');

        $query = new Query();
        $query->addFacet($facet);
        $query->setQuery(new MatchAll());

        $index->refresh();

        $response = $type->search($query);
        $facets = $response->getFacets();

        $this->assertEquals(55, $facets['stats']['total']);
        $this->assertEquals(10, $facets['stats']['min']);
        $this->assertEquals(45, $facets['stats']['max']);
    }

    public function testStatisticalWithSetFields()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('helloworld');

        $doc = new Document(1, array('price' => 10, 'price2' => 20));
        $type->addDocument($doc);
        $doc = new Document(2, array('price' => 35, 'price2' => 70));
        $type->addDocument($doc);
        $doc = new Document(2, array('price' => 45, 'price2' => 90));
        $type->addDocument($doc);

        $facet = new Statistical('stats');
        $facet->setFields(array('price','price2'));

        $query = new Query();
        $query->addFacet($facet);
        $query->setQuery(new MatchAll());

        $index->refresh();

        $response = $type->search($query);
        $facets = $response->getFacets();

        $this->assertEquals(165, $facets['stats']['total']);
        $this->assertEquals(10, $facets['stats']['min']);
        $this->assertEquals(90, $facets['stats']['max']);
    }

    /**
     * @todo
     */
    public function testStatisticalWithSetScript()
    {
        $this->markTestIncomplete('Test for setting the script value');
    }
}
