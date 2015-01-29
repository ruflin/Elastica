<?php

namespace Elastica\Test\Facet;

use Elastica\Document;
use Elastica\Facet\DateHistogram;
use Elastica\Query;
use Elastica\Query\MatchAll;
use Elastica\Test\Base as BaseTest;
use Elastica\Type\Mapping;

class DateHistogramTest extends BaseTest
{
    public function testClassHierarchy()
    {
        $facet = new DateHistogram('dateHist1');
        $this->assertInstanceOf('Elastica\Facet\Histogram', $facet);
        $this->assertInstanceOf('Elastica\Facet\AbstractFacet', $facet);
        unset($facet);
    }

    public function testTest()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('helloworld');

        $mapping = new Mapping($type, array(
                'name' => array('type' => 'string', 'store' => 'no'),
                'dtmPosted' => array('type' => 'date', 'store' => 'no', 'format' => 'yyyy-MM-dd HH:mm:ss'),
            ));
        $type->setMapping($mapping);

        $doc = new Document(1, array('name' => 'nicolas ruflin', 'dtmPosted' => "2011-06-23 21:53:00"));
        $type->addDocument($doc);
        $doc = new Document(2, array('name' => 'raul martinez jr', 'dtmPosted' => "2011-06-23 09:53:00"));
        $type->addDocument($doc);
        $doc = new Document(3, array('name' => 'rachelle clemente', 'dtmPosted' => "2011-07-08 08:53:00"));
        $type->addDocument($doc);
        $doc = new Document(4, array('name' => 'elastica search', 'dtmPosted' => "2011-07-08 01:53:00"));
        $type->addDocument($doc);

        $facet = new DateHistogram('dateHist1');
        $facet->setInterval("day");
        $facet->setField("dtmPosted");

        $query = new Query();
        $query->addFacet($facet);
        $query->setQuery(new MatchAll());
        $index->refresh();

        $response = $type->search($query);
        $facets = $response->getFacets();

        $this->assertEquals(4, $response->getTotalHits());
        $this->assertEquals(2, count($facets['dateHist1']['entries']));
    }
}
