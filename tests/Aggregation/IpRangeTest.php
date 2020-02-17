<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\IpRange;
use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;

/**
 * @internal
 */
class IpRangeTest extends BaseAggregationTest
{
    /**
     * @group functional
     */
    public function testIpRangeAggregation(): void
    {
        $agg = new IpRange('ip', 'address');
        $agg->addRange('192.168.1.101');
        $agg->addRange(null, '192.168.1.200');

        $cidrRange = '192.168.1.0/24';
        $agg->addMaskRange($cidrRange);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('ip');

        foreach ($results['buckets'] as $bucket) {
            if (\array_key_exists('key', $bucket) && $bucket['key'] == $cidrRange) {
                // the CIDR mask
                $this->assertEquals(3, $bucket['doc_count']);
            } else {
                // the normal ip ranges
                $this->assertEquals(2, $bucket['doc_count']);
            }
        }
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();
        $index->setMapping(new Mapping([
            'address' => ['type' => 'ip'],
        ]));

        $index->addDocuments([
            new Document(1, ['address' => '192.168.1.100']),
            new Document(2, ['address' => '192.168.1.150']),
            new Document(3, ['address' => '192.168.1.200']),
        ]);

        $index->refresh();

        return $index;
    }
}
