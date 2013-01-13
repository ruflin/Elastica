<?php

namespace Elastica\Test\Facet;

use Elastica\Document;
use Elastica\Facet\QueryFacet;
use Elastica\Query;
use Elastica\Query\TermQuery;
use Elastica\Test\Base as BaseTest;

class QueryTest extends BaseTest
{
    public function testFilter()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('helloworld');

        $type->addDocument(new Document(1, array('color' => 'red')));
        $type->addDocument(new Document(2, array('color' => 'green')));
        $type->addDocument(new Document(3, array('color' => 'blue')));

        $index->refresh();

        $termQuery = new TermQuery(array('color' => 'red'));

        $facet = new QueryFacet('test');
        $facet->setQuery($termQuery);

        $query = new Query();
        $query->addFacet($facet);

        $resultSet = $type->search($query);

        $facets = $resultSet->getFacets();

        $this->assertEquals(1, $facets['test']['count']);
    }
}
