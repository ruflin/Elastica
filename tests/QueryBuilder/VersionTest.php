<?php

namespace Elastica\Test\QueryBuilder;

use Elastica\QueryBuilder\DSL;
use Elastica\QueryBuilder\Version;
use Elastica\Test\Base as BaseTest;

/**
 * @group unit
 *
 * @internal
 */
class VersionTest extends BaseTest
{
    public function testVersions(): void
    {
        $dsl = [
            new DSL\Query(),
            new DSL\Aggregation(),
            new DSL\Suggest(),
            new DSL\Collapse(),
        ];

        $versions = [
            new Version\Version700(),
            new Version\Latest(),
        ];

        foreach ($versions as $version) {
            $this->assertVersions($version, $dsl);
        }
    }

    private function assertVersions(Version $version, array $dsl): void
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

        foreach ($version->getCollapsers() as $collapser) {
            $this->assertTrue(
                \method_exists($dsl[3], $collapser),
                'suggester "'.$collapser.'" in '.\get_class($version).' must be defined in '.\get_class($dsl[3])
            );
        }
    }
}
