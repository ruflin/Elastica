<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\DateHistogram;
use Elastica\Document;
use Elastica\Query;
use Elastica\Type\Mapping;

class DateHistogramTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $type->setMapping(new Mapping(null, array(
            'created' => array('type' => 'date'),
        )));

        $type->addDocuments(array(
            new Document(1, array('created' => '2014-01-29T00:20:00')),
            new Document(2, array('created' => '2014-01-29T02:20:00')),
            new Document(3, array('created' => '2014-01-29T03:20:00')),
        ));

        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testDateHistogramAggregation()
    {
        $agg = new DateHistogram('hist', 'created', '1h');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('hist');

        $docCount = 0;
        $nonDocCount = 0;
        foreach ($results['buckets'] as $bucket) {
            if ($bucket['doc_count'] == 1) {
                ++$docCount;
            } else {
                ++$nonDocCount;
            }
        }
        // 3 Documents that were added
        $this->assertEquals(3, $docCount);
        // 1 document that was generated in between for the missing hour
        $this->assertEquals(1, $nonDocCount);
    }

    /**
     * @group unit
     */
    public function testSetOffset()
    {
        $agg = new DateHistogram('hist', 'created', '1h');

        $agg->setOffset('3m');

        $expected = array(
            'date_histogram' => array(
                'field' => 'created',
                'interval' => '1h',
                'offset' => '3m',
            ),
        );

        $this->assertEquals($expected, $agg->toArray());

        $this->assertInstanceOf('Elastica\Aggregation\DateHistogram', $agg->setOffset('3m'));
    }

    /**
     * @group functional
     */
    public function testSetOffsetWorks()
    {
        $this->_checkVersion('1.5');

        $agg = new DateHistogram('hist', 'created', '1m');
        $agg->setOffset('+40s');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('hist');

        $this->assertEquals('2014-01-29T00:19:40.000Z', $results['buckets'][0]['key_as_string']);
    }

    /**
     * @group unit
     */
    public function testSetTimezone()
    {
        $agg = new DateHistogram('hist', 'created', '1h');

        $agg->setTimezone('-02:30');

        $expected = array(
            'date_histogram' => array(
                'field' => 'created',
                'interval' => '1h',
                'time_zone' => '-02:30',
            ),
        );

        $this->assertEquals($expected, $agg->toArray());

        $this->assertInstanceOf('Elastica\Aggregation\DateHistogram', $agg->setTimezone('-02:30'));
    }
}
