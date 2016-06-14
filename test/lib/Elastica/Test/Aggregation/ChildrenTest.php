<?php
namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Children;
use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Query;
use Elastica\Type\Mapping;

class ChildrenTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();

        // add employee type - child
        $employeeType = $index->getType('employee');
        $employeeMapping = new Mapping($employeeType,
            array(
                'name' => array('type' => 'string'),
            )
        );
        $employeeMapping->setParent('company');
        $employeeType->setMapping($employeeMapping);

        // add company type - parent
        $companyType = $index->getType('company');
        $companyMapping = new Mapping($companyType,
            array(
                'name' => array('type' => 'string'),
            )
        );
        $companyType->setMapping($companyMapping);

        // add company documents
        $companyType->addDocuments(array(
            new Document(1, array('name' => 'Company1')),
            new Document(2, array('name' => 'Company2')),
        ));

        $employee1 = new Document(1, array(
            'name' => 'foo',
        ));
        $employee2 = new Document(2, array(
            'name' => 'bar',
        ));
        $employee3 = new Document(3, array(
            'name' => 'foo',
        ));
        $employee4 = new Document(4, array(
            'name' => 'baz',
        ));
        $employee5 = new Document(5, array(
            'name' => 'foo',
        ));

        // add employee documents and set parent
        $employeeType->addDocuments(array(
            $employee1->setParent(1),
            $employee2->setParent(1),
            $employee3->setParent(1),
            $employee4->setParent(2),
            $employee5->setParent(2),
        ));
        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testChildrenAggregation()
    {
        $agg = new Children('children');
        $agg->setType('employee');

        $names = new Terms('name');
        $names->setField('name');

        $agg->addAggregation($names);

        $query = new Query();
        $query->addAggregation($agg);

        $companyType = $this->_getIndexForTest()->getType('company');
        $aggregations = $companyType->search($query)->getAggregations();

        // check children aggregation exists
        $this->assertArrayHasKey('children', $aggregations);

        $childrenAggregations = $aggregations['children'];

        // check names aggregation exists inside children aggregation
        $this->assertArrayHasKey('name', $childrenAggregations);
        $this->assertCount(3, $childrenAggregations['name']['buckets']);

        // check names aggregation works inside children aggregation
        $names = array(
            array('key' => 'foo', 'doc_count' => 3),
            array('key' => 'bar', 'doc_count' => 1),
            array('key' => 'baz', 'doc_count' => 1),
        );
        $this->assertEquals($names, $childrenAggregations['name']['buckets']);
    }
}
