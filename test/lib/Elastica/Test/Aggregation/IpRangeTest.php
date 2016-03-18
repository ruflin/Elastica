<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\IpRange;
use Elastica\Document;
use Elastica\Query;
use Elastica\Type\Mapping;

class IpRangeTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $type->setMapping(new Mapping(null, array(
            'address' => array('type' => 'ip'),
        )));

        $type->addDocuments(array(
            new Document(1, array('address' => '192.168.1.100')),
            new Document(2, array('address' => '192.168.1.150')),
            new Document(3, array('address' => '192.168.1.200')),
        ));

        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testIpRangeAggregation()
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
            if (array_key_exists('key', $bucket) && $bucket['key'] == $cidrRange) {
                // the CIDR mask
                $this->assertEquals(3, $bucket['doc_count']);
            } else {
                // the normal ip ranges
                $this->assertEquals(2, $bucket['doc_count']);
            }
        }
    }
}
