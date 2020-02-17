<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\SignificantTerms;
use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;
use Elastica\Query\Terms;

/**
 * @internal
 */
class SignificantTermsTest extends BaseAggregationTest
{
    /**
     * @group functional
     */
    public function testSignificantTermsAggregation(): void
    {
        $agg = new SignificantTerms('significantTerms');
        $agg->setField('temperature');
        $agg->setSize(1);

        $termsQuery = new Terms();
        $termsQuery->setTerms('color', ['blue', 'red', 'green', 'yellow', 'white']);

        $query = new Query($termsQuery);
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('significantTerms');

        $this->assertCount(1, $results['buckets']);
        $this->assertEquals(63, $results['buckets'][0]['doc_count']);
        $this->assertEquals(79, $results['buckets'][0]['bg_count']);
        $this->assertEquals('1500', $results['buckets'][0]['key']);
    }

    /**
     * @group functional
     */
    public function testSignificantTermsAggregationWithBackgroundFilter(): void
    {
        $agg = new SignificantTerms('significantTerms');
        $agg->setField('temperature');
        $agg->setSize(1);
        $termsFilter = new Terms();
        $termsFilter->setTerms('color', ['blue', 'red', 'green', 'yellow']);
        $agg->setBackgroundFilter($termsFilter);

        $termsQuery = new Terms();
        $termsQuery->setTerms('color', ['blue', 'red', 'green', 'yellow', 'white']);

        $query = new Query($termsQuery);
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('significantTerms');

        $this->assertEquals(15, $results['buckets'][0]['doc_count']);
        $this->assertEquals(12, $results['buckets'][0]['bg_count']);
        $this->assertEquals('4500', $results['buckets'][0]['key']);
    }

    /**
     * @group functional
     */
    public function testSignificantTermsAggregationWithBackgroundFilterWithLegacyFilter(): void
    {
        $agg = new SignificantTerms('significantTerms');
        $agg->setField('temperature');
        $agg->setSize(1);
        $termsFilter = new Terms();
        $termsFilter->setTerms('color', ['blue', 'red', 'green', 'yellow']);
        $agg->setBackgroundFilter($termsFilter);

        $termsQuery = new Terms();
        $termsQuery->setTerms('color', ['blue', 'red', 'green', 'yellow', 'white']);

        $query = new Query($termsQuery);
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('significantTerms');

        $this->assertEquals(15, $results['buckets'][0]['doc_count']);
        $this->assertEquals(12, $results['buckets'][0]['bg_count']);
        $this->assertEquals('4500', $results['buckets'][0]['key']);
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();
        $colors = ['blue', 'blue', 'red', 'red', 'green', 'yellow', 'white', 'cyan', 'magenta'];
        $temperatures = [1500, 1500, 1500, 1500, 2500, 3500, 4500, 5500, 6500, 7500, 7500, 8500, 9500];

        $mapping = new Mapping([
            'color' => ['type' => 'keyword'],
            'temperature' => ['type' => 'keyword'],
        ]);
        $index->setMapping($mapping);

        $docs = [];
        for ($i = 0; $i < 250; ++$i) {
            $docs[] = new Document($i, ['color' => $colors[$i % \count($colors)], 'temperature' => $temperatures[$i % \count($temperatures)]]);
        }
        $index->addDocuments($docs);
        $index->refresh();

        return $index;
    }
}
