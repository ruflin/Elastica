<?php
namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Filter\Term;
use Elastica\Query;
use Elastica\Query\Match;
use Elastica\Test\Base as BaseTest;

class PostFilterTest extends BaseTest
{
    protected function _getTestIndex()
    {
        $index = $this->_createIndex();
        $docs = array(
            new Document(1, array('color' => 'green', 'make' => 'ford')),
            new Document(2, array('color' => 'blue', 'make' => 'volvo')),
            new Document(3, array('color' => 'red', 'make' => 'ford')),
            new Document(4, array('color' => 'green', 'make' => 'renault')),
        );
        $index->getType('test')->addDocuments($docs);
        $index->refresh();

        return $index;
    }

    /**
     * @group unit
     */
    public function testToArray()
    {
        $query = new Query();

        $post_filter = new Term(array('color' => 'green'));
        $query->setPostFilter($post_filter);

        $data = $query->toArray();

        $this->assertArrayHasKey('post_filter', $data);
        $this->assertEquals(array('term' => array('color' => 'green')), $data['post_filter']);
    }

    /**
     * @group functional
     */
    public function testQuery()
    {
        $query = new Query();

        $match = new Match();
        $match->setField('make', 'ford');

        $query->setQuery($match);

        $filter = new Term();
        $filter->setTerm('color', 'green');

        $query->setPostFilter($filter);

        $results = $this->_getTestIndex()->search($query);

        $this->assertEquals(1, $results->getTotalHits());
    }
}
