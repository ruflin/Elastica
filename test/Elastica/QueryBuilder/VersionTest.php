<?php

namespace Elastica\Test\QueryBuilder;

use Elastica\QueryBuilder\DSL;
use Elastica\QueryBuilder\Version;
use Elastica\Test\Base as BaseTest;

class VersionTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testVersions()
    {
        $dsl = [
            new DSL\Query(),
            new DSL\Aggregation(),
            new DSL\Suggest(),
        ];

        $versions = [
            new Version\Version240(),
            new Version\Latest(),
        ];

        foreach ($versions as $version) {
            $this->assertVersions($version, $dsl);
        }
    }

    private function assertVersions(Version $version, array $dsl)
    {
        foreach ($version->getQueries() as $query) {
            $this->assertTrue(
                \method_exists($dsl[0], $query),
                'query "'.$query.'" in '.\get_class($version).' must be defined in '.\get_class($dsl[0])
            );
        }

        foreach ($version->getAggregations() as $aggregation) {
            $this->assertTrue(
                \method_exists($dsl[1], $aggregation),
                'aggregation "'.$aggregation.'" in '.\get_class($version).' must be defined in '.\get_class($dsl[2])
            );
        }

        foreach ($version->getSuggesters() as $suggester) {
            $this->assertTrue(
                \method_exists($dsl[2], $suggester),
                'suggester "'.$suggester.'" in '.\get_class($version).' must be defined in '.\get_class($dsl[2])
            );
        }
    }
}
