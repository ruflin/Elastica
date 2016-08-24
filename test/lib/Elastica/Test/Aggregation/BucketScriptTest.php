<?php
namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\BucketScript;
use Elastica\Aggregation\Max;
use Elastica\Aggregation\Histogram;
use Elastica\Document;
use Elastica\Query;

class BucketScriptTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();

        $index->getType('test')->addDocuments([
            Document::create(['weight' => 60, 'height' => 180, 'age' => 25]),
            Document::create(['weight' => 65, 'height' => 156, 'age' => 32]),
            Document::create(['weight' => 50, 'height' => 155, 'age' => 45]),
        ]);

        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testBucketScriptAggregation()
    {
        $this->_checkScriptInlineSetting();

        $bucketScriptAggregation = new BucketScript(
            'result',
            [
                'divisor' => 'max_weight',
                'dividend' => 'max_height',
            ],
            'dividend / divisor'
        );

        $histogramAggregation = new Histogram('age_groups', 'age', 10);

        $histogramAggregation
            ->addAggregation((new Max('max_weight'))->setField('weight'))
            ->addAggregation((new Max('max_height'))->setField('height'))
            ->addAggregation($bucketScriptAggregation);

        $query = Query::create([])->addAggregation($histogramAggregation);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('age_groups');

        $this->assertEquals(3, $results['buckets'][0]['result']['value']);
        $this->assertEquals(2.4, $results['buckets'][1]['result']['value']);
        $this->assertEquals(3.1, $results['buckets'][2]['result']['value']);
    }
}
