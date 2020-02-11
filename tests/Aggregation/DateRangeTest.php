<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\DateRange;
use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;

class DateRangeTest extends BaseAggregationTest
{
    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();
        $index->setMapping(new Mapping([
            'created' => ['type' => 'date', 'format' => 'epoch_millis'],
        ]));

        $index->addDocuments([
            new Document(1, ['created' => 1390962135000]),
            new Document(2, ['created' => 1390965735000]),
            new Document(3, ['created' => 1390954935000]),
        ]);

        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testDateRangeAggregation()
    {
        $agg = new DateRange('date');
        $agg->setField('created');
        $agg->addRange(1390958535000)->addRange(null, 1390958535000);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('date');

        foreach ($results['buckets'] as $bucket) {
            if (\array_key_exists('to', $bucket)) {
                $this->assertEquals(1, $bucket['doc_count']);
            } elseif (\array_key_exists('from', $bucket)) {
                $this->assertEquals(2, $bucket['doc_count']);
            }
        }
    }

    /**
     * @group functional
     */
    public function testDateRangeSetFormat()
    {
        $agg = new DateRange('date');
        $agg->setField('created');
        $agg->addRange(1390958535000)->addRange(null, 1390958535000);
        $agg->setFormat('epoch_millis');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('date');

        $this->assertEquals('1390958535000', $results['buckets'][0]['to_as_string']);
    }

    /**
     * @group functional
     */
    public function testDateRangeSetFormatAccordingToFormatTargetField()
    {
        $agg = new DateRange('date');
        $agg->setField('created');
        $agg->addRange(1390958535000)->addRange(null, 1390958535000);
        $agg->setFormat('m-d-y');

        $query = new Query();
        $query->addAggregation($agg);

        try {
            $results = $this->_getIndexForTest()->search($query)->getAggregation('date');
            $this->fail('Should throw exception to and from parameters in date_range aggregation are interpreted according of the target field');
        } catch (ResponseException $e) {
            $error = $e->getResponse()->getFullError();

            $this->assertContains('search_phase_execution_exception', $error['type']);
            $this->assertContains('failed to parse date field', $error['root_cause'][0]['reason']);
        }
    }
}
