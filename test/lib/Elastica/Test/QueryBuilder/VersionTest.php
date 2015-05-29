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
        $dsl = array(
            new DSL\Query(),
            new DSL\Filter(),
            new DSL\Aggregation(),
            new DSL\Suggest(),
        );

        $versions = array(
            new Version\Version090(),
            new Version\Version100(),
            new Version\Version110(),
            new Version\Version120(),
            new Version\Version130(),
            new Version\Version140(),
            new Version\Version150(),
        );

        foreach ($versions as $version) {
            $this->assertVersions($version, $dsl);
        }
    }

    private function assertVersions(Version $version, array $dsl)
    {
        foreach ($version->getQueries() as $query) {
            $this->assertTrue(
                method_exists($dsl[0], $query),
                'query "'.$query.'" in '.get_class($version).' must be defined in '.get_class($dsl[0])
            );
        }

        foreach ($version->getFilters() as $filter) {
            $this->assertTrue(
                method_exists($dsl[1], $filter),
                'filter "'.$filter.'" in '.get_class($version).' must be defined in '.get_class($dsl[1])
            );
        }

        foreach ($version->getAggregations() as $aggregation) {
            $this->assertTrue(
                method_exists($dsl[2], $aggregation),
                'aggregation "'.$aggregation.'" in '.get_class($version).' must be defined in '.get_class($dsl[2])
            );
        }

        foreach ($version->getSuggesters() as $suggester) {
            $this->assertTrue(
                method_exists($dsl[3], $suggester),
                'suggester "'.$suggester.'" in '.get_class($version).' must be defined in '.get_class($dsl[3])
            );
        }
    }
}
