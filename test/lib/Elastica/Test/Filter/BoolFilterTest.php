<?php

namespace Elastica\Test\Filter;

use Elastica\Document;
use Elastica\Filter\BoolFilter;
use Elastica\Filter\Ids;
use Elastica\Filter\Term;
use Elastica\Filter\Terms;
use Elastica\Query;
use Elastica\Test\DeprecatedClassBase as BaseTest;

class BoolFilterTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testDeprecated()
    {
        $reflection = new \ReflectionClass(new BoolFilter());
        $this->assertFileDeprecated($reflection->getFileName(), 'Deprecated: Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html');
    }

    /**
     * @return array
     */
    public function getTestToArrayData()
    {
        $this->hideDeprecated();

        $out = array();

        // case #0
        $mainBool = new BoolFilter();

        $idsFilter1 = new Ids();
        $idsFilter1->setIds(1);
        $idsFilter2 = new Ids();
        $idsFilter2->setIds(2);
        $idsFilter3 = new Ids();
        $idsFilter3->setIds(3);

        $childBool = new BoolFilter();

        $childBool->addShould(array($idsFilter1, $idsFilter2));
        $mainBool->addShould(array($childBool, $idsFilter3));

        $expectedArray = array(
            'bool' => array(
                'should' => array(
                    array(
                        array(
                            'bool' => array(
                                'should' => array(
                                    array(
                                        $idsFilter1->toArray(),
                                        $idsFilter2->toArray(),
                                    ),
                                ),
                            ),
                        ),
                        $idsFilter3->toArray(),
                    ),
                ),
            ),
        );
        $out[] = array($mainBool, $expectedArray);

        // case #1 _cache parameter should be supported
        $bool = new BoolFilter();
        $terms = new Terms('field1', array('value1', 'value2'));
        $termsNot = new Terms('field2', array('value1', 'value2'));
        $bool->addMust($terms);
        $bool->addMustNot($termsNot);
        $bool->setCached(true);
        $bool->setCacheKey('my-cache-key');
        $expected = array(
            'bool' => array(
                'must' => array(
                    $terms->toArray(),
                ),
                'must_not' => array(
                    $termsNot->toArray(),
                ),
                '_cache' => true,
                '_cache_key' => 'my-cache-key',
            ),
        );
        $out[] = array($bool, $expected);

        $this->showDeprecated();

        return $out;
    }

    /**
     * @group unit
     * @dataProvider getTestToArrayData()
     *
     * @param bool  $bool
     * @param array $expectedArray
     */
    public function testToArray(BoolFilter $bool, $expectedArray)
    {
        $this->assertEquals($expectedArray, $bool->toArray());
    }

    /**
     * @group functional
     */
    public function testBoolFilter()
    {
        $index = $this->_createIndex();
        $type = $index->getType('book');

        //index some test data
        $type->addDocuments(array(
            new Document(1, array('author' => 'Michael Shermer', 'title' => 'The Believing Brain', 'publisher' => 'Robinson')),
            new Document(2, array('author' => 'Jared Diamond', 'title' => 'Guns, Germs and Steel', 'publisher' => 'Vintage')),
            new Document(3, array('author' => 'Jared Diamond', 'title' => 'Collapse', 'publisher' => 'Penguin')),
            new Document(4, array('author' => 'Richard Dawkins', 'title' => 'The Selfish Gene', 'publisher' => 'OUP Oxford')),
            new Document(5, array('author' => 'Anthony Burges', 'title' => 'A Clockwork Orange', 'publisher' => 'Penguin')),
        ));

        $index->refresh();

        //use the terms lookup feature to query for some data
        //build query
        //must
        //  should
        //      author = jared
        //      author = richard
        //  must_not
        //      publisher = penguin

        //construct the query
        $query = new Query();
        $mainBoolFilter = new BoolFilter();
        $shouldFilter = new BoolFilter();
        $authorFilter1 = new Term();
        $authorFilter1->setTerm('author', 'jared');
        $authorFilter2 = new Term();
        $authorFilter2->setTerm('author', 'richard');
        $shouldFilter->addShould(array($authorFilter1, $authorFilter2));

        $mustNotFilter = new BoolFilter();
        $publisherFilter = new Term();
        $publisherFilter->setTerm('publisher', 'penguin');
        $mustNotFilter->addMustNot($publisherFilter);

        $mainBoolFilter->addMust(array($shouldFilter, $mustNotFilter));
        $query->setPostFilter($mainBoolFilter);
        //execute the query
        $results = $index->search($query);

        //check the number of results
        $this->assertEquals($results->count(), 2, 'Bool filter with child Bool filters: number of results check');

        //count compare the id's
        $ids = array();
        /** @var \Elastica\Result $result **/
        foreach ($results as $result) {
            $ids[] = $result->getId();
        }
        $this->assertEquals($ids, array('2', '4'), 'Bool filter with child Bool filters: result ID check');

        $index->delete();
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testAddMustInvalidException()
    {
        $filter = new BoolFilter();
        $filter->addMust('fail!');
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testAddMustNotInvalidException()
    {
        $filter = new BoolFilter();
        $filter->addMustNot('fail!');
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testAddShouldInvalidException()
    {
        $filter = new BoolFilter();
        $filter->addShould('fail!');
    }

    /**
     * Small unit test to check if also the old object name works.
     *
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testOldObject()
    {
        if (version_compare(phpversion(), 7, '>=')) {
            self::markTestSkipped('These objects are not supported in PHP 7');
        }

        $filter = new \Elastica\Filter\Bool();

        $filter->addShould('fail!');
    }

    /**
     * @group unit
     */
    public function testOldObjectDeprecated()
    {
        if (version_compare(phpversion(), 7, '>=')) {
            self::markTestSkipped('These objects are not supported in PHP 7');
        }

        $reflection = new \ReflectionClass(new \Elastica\Filter\Bool());
        $this->assertFileDeprecated($reflection->getFileName(), 'Deprecated: Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html');
    }
}
