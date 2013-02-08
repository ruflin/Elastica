<?php

namespace Elastica\Test\Facet;

use Elastica\Document;
use Elastica\Facet\Terms;
use Elastica\Query;
use Elastica\Query\MatchAll;
use Elastica\Test\Base as BaseTest;

class TermsTest extends BaseTest
{
    public function testQuery()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('helloworld');

        $doc = new Document(1, array('name' => 'nicolas ruflin'));
        $type->addDocument($doc);
        $doc = new Document(2, array('name' => 'ruflin test'));
        $type->addDocument($doc);
        $doc = new Document(2, array('name' => 'nicolas helloworld'));
        $type->addDocument($doc);

        $facet = new Terms('test');
        $facet->setField('name');

        $query = new Query();
        $query->addFacet($facet);
        $query->setQuery(new MatchAll());

        $index->refresh();

        $response = $type->search($query);
        $facets = $response->getFacets();

        $this->assertEquals(3, count($facets['test']['terms']));
    }

    public function testFacetScript()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('helloworld');

        $doc = new Document(1, array('name' => 'rodolfo', 'last_name' => 'moraes'));
        $type->addDocument($doc);
        $doc = new Document(2, array('name' => 'jose', 'last_name' => 'honjoya'));
        $type->addDocument($doc);

        $facet = new Terms('test');
        $facet->setField('name');
        $facet->setScript('term + " "+doc["last_name"].value');

        $query = new Query();
        $query->addFacet($facet);
        $query->setQuery(new MatchAll());

        $index->refresh();

        $response = $type->search($query);
        $facets = $response->getFacets();

        $this->assertEquals(2, count($facets['test']['terms']));
    }
}
