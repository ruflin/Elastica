<?php
namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\SignificantTerms;
use Elastica\Document;
use Elastica\Filter\Terms as TermsFilter;
use Elastica\Query;
use Elastica\Query\Terms;

class SignificantTermsTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();
        $colors = array('blue', 'blue', 'red', 'red', 'green', 'yellow', 'white', 'cyan', 'magenta');
        $temperatures = array(1500, 1500, 1500, 1500, 2500, 3500, 4500, 5500, 6500, 7500, 7500, 8500, 9500);
        $docs = array();
        for ($i = 0;$i < 250;++$i) {
            $docs[] = new Document($i, array('color' => $colors[$i % count($colors)], 'temperature' => $temperatures[$i % count($temperatures)]));
        }
        $index->getType('test')->addDocuments($docs);
        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testSignificantTermsAggregation()
    {
        $agg = new SignificantTerms('significantTerms');
        $agg->setField('temperature');
        $agg->setSize(1);

        $termsQuery = new Terms();
        $termsQuery->setTerms('color', array('blue', 'red', 'green', 'yellow', 'white'));

        $query = new Query($termsQuery);
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('significantTerms');

        $this->assertEquals(1, count($results['buckets']));
        $this->assertEquals(63, $results['buckets'][0]['doc_count']);
        $this->assertEquals(79, $results['buckets'][0]['bg_count']);
        $this->assertEquals('1500', $results['buckets'][0]['key_as_string']);
    }

    /**
     * @group functional
     */
    public function testSignificantTermsAggregationWithBackgroundFilter()
    {
        $agg = new SignificantTerms('significantTerms');
        $agg->setField('temperature');
        $agg->setSize(1);
        $termsFilter = new TermsFilter();
        $termsFilter->setTerms('color', array('blue', 'red', 'green', 'yellow'));
        $agg->setBackgroundFilter($termsFilter);

        $termsQuery = new Terms();
        $termsQuery->setTerms('color', array('blue', 'red', 'green', 'yellow', 'white'));

        $query = new Query($termsQuery);
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('significantTerms');

        $this->assertEquals(15, $results['buckets'][0]['doc_count']);
        $this->assertEquals(12, $results['buckets'][0]['bg_count']);
        $this->assertEquals('4500', $results['buckets'][0]['key_as_string']);
    }
}
