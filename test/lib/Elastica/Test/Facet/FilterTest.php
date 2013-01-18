<?php

namespace Elastica\Test\Facet;

use Elastica\Document;
use Elastica\Filter\Term;
use Elastica\Facet\Filter;
use Elastica\Query;
use Elastica\Test\Base as BaseTest;

class FilterTest extends BaseTest
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

        $filter = new Term(array('color' => 'red'));

        $facet = new Filter('test');
        $facet->setFilter($filter);

        $query = new Query();
        $query->addFacet($facet);

        $resultSet = $type->search($query);

        $facets = $resultSet->getFacets();

        $this->assertEquals(1, $facets['test']['count']);
    }
}
