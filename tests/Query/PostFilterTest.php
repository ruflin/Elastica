<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use Elastica\Query\MatchQuery;
use Elastica\Query\Term;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class PostFilterTest extends BaseTest
{
    #[Group('unit')]
    public function testToArray(): void
    {
        $query = new Query();

        $postFilter = new Term(['color' => 'green']);
        $query->setPostFilter($postFilter);

        $data = $query->toArray();

        $this->assertArrayHasKey('post_filter', $data);
        $this->assertEquals(['term' => ['color' => 'green']], $data['post_filter']);
    }

    #[Group('functional')]
    public function testQuery(): void
    {
        $query = new Query();

        $match = new MatchQuery();
        $match->setField('make', 'ford');

        $query->setQuery($match);

        $filter = new Term();
        $filter->setTerm('color', 'green');

        $query->setPostFilter($filter);

        $this->assertEquals(1, $this->_getTestIndex()->count($query));
    }

    protected function _getTestIndex(): Index
    {
        $index = $this->_createIndex();
        $docs = [
            new Document('1', ['color' => 'green', 'make' => 'ford']),
            new Document('2', ['color' => 'blue', 'make' => 'volvo']),
            new Document('3', ['color' => 'red', 'make' => 'ford']),
            new Document('4', ['color' => 'green', 'make' => 'renault']),
        ];
        $index->addDocuments($docs);
        $index->refresh();

        return $index;
    }
}
