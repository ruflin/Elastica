<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastica\Aggregation\DateRange;
use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class DateRangeTest extends BaseAggregationTestCase
{
    #[Group('unit')]
    public function testSetTimezone(): void
    {
        $agg = (new DateRange('name'))
            ->setField('date')
            ->setTimezone('CET')
        ;

        $expected = [
            'date_range' => [
                'field' => 'date',
                'time_zone' => 'CET',
            ],
        ];

        $this->assertEquals($expected, $agg->toArray());
    }

    #[Group('functional')]
    public function testDateRangeAggregation(): void
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

    #[Group('functional')]
    public function testDateRangeAggregationWithMissing(): void
    {
        $agg = new DateRange('date');
        $agg->setField('created');
        $agg->addRange(1390958535000)->addRange(null, 1390958535000);
        $agg->setMissing(1390958534000);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('date');

        foreach ($results['buckets'] as $bucket) {
            if (\array_key_exists('to', $bucket)) {
                $this->assertEquals(2, $bucket['doc_count']);
            } elseif (\array_key_exists('from', $bucket)) {
                $this->assertEquals(2, $bucket['doc_count']);
            }
        }
    }

    #[Group('functional')]
    public function testDateRangeKeyedAggregation(): void
    {
        $agg = new DateRange('date');
        $agg->setField('created');
        $agg->setKeyed();
        $agg->addRange(1390958535000)->addRange(null, 1390958535000);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('date');

        $expected = [
            '*-1390958535000',
            '1390958535000-*',
        ];
        $this->assertSame($expected, \array_keys($results['buckets']));
    }

    #[Group('functional')]
    public function testDateRangeSetFormat(): void
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

    #[Group('functional')]
    public function testDateRangeSetFormatAccordingToFormatTargetField(): void
    {
        $agg = new DateRange('date');
        $agg->setField('created');
        $agg->addRange(1390958535000)->addRange(null, 1390958535000);
        $agg->setFormat('m-d-y');

        $query = new Query();
        $query->addAggregation($agg);

        try {
            $this->_getIndexForTest()->search($query)->getAggregation('date');
            $this->fail('Should throw exception to and from parameters in date_range aggregation are interpreted according of the target field');
        } catch (ClientResponseException $e) {
            $error = \json_decode((string) $e->getResponse()->getBody(), true)['error'] ?? null;

            $this->assertSame('search_phase_execution_exception', $error['type']);
            $this->assertStringStartsWith('failed to parse date field', $error['root_cause'][0]['reason']);
        }
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();
        $index->setMapping(new Mapping([
            'created' => ['type' => 'date', 'format' => 'epoch_millis'],
        ]));

        $index->addDocuments([
            new Document('1', ['created' => 1390962135000]),
            new Document('2', ['created' => 1390965735000]),
            new Document('3', ['created' => 1390954935000]),
            new Document('4', ['anything' => 'anything']),
        ]);

        $index->refresh();

        return $index;
    }
}
