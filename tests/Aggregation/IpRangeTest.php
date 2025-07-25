<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\IpRange;
use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class IpRangeTest extends BaseAggregationTestCase
{
    #[Group('functional')]
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
            if (\array_key_exists('key', $bucket) && $bucket['key'] === $cidrRange) {
                // the CIDR mask
                $this->assertSame(3, $bucket['doc_count']);
            } else {
                // the normal ip ranges
                $this->assertSame(2, $bucket['doc_count']);
            }
        }
    }

    #[Group('functional')]
    public function testIpRangeKeyedAggregation(): void
    {
        $agg = new IpRange('ip', 'address');
        $agg->addRange('192.168.1.101');
        $agg->addRange(null, '192.168.1.200');
        $agg->setKeyed();

        $cidrRange = '192.168.1.0/24';
        $agg->addMaskRange($cidrRange);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('ip');

        $expected = [
            '*-192.168.1.200',
            '192.168.1.0/24',
            '192.168.1.101-*',
        ];
        $this->assertSame($expected, \array_keys($results['buckets']));
    }

    /**
     * @group unit
     */
    public function testIpRangeAggregationWithKey(): void
    {
        $agg = new IpRange('ip', 'address');
        $agg->addRange('192.168.1.101', null, 'first');
        $agg->addRange(null, '192.168.1.200', 'second');
        $agg->addMaskRange('192.168.1.0/24', 'mask');

        $expected = [
            'ip_range' => [
                'field' => 'address',
                'ranges' => [
                    [
                        'from' => '192.168.1.101',
                        'key' => 'first',
                    ],
                    [
                        'to' => '192.168.1.200',
                        'key' => 'second',
                    ],
                    [
                        'mask' => '192.168.1.0/24',
                        'key' => 'mask',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $agg->toArray());
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();
        $index->setMapping(new Mapping([
            'address' => ['type' => 'ip'],
        ]));

        $index->addDocuments([
            new Document('1', ['address' => '192.168.1.100']),
            new Document('2', ['address' => '192.168.1.150']),
            new Document('3', ['address' => '192.168.1.200']),
        ]);

        $index->refresh();

        return $index;
    }
}
