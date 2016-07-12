<?php
namespace Elastica\Test;

use Elastica\Document;
use Elastica\Index;
use Elastica\Percolator;
use Elastica\Query;
use Elastica\Query\Term;
use Elastica\Test\Base as BaseTest;
use Elastica\Type;

class PercolatorTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testConstruct()
    {
        $index = $this->_createIndex();
        $percolatorName = $index->getName();

        $percolator = new Percolator($index);

        $query = new Term(['field1' => 'value1']);
        $response = $percolator->registerQuery($percolatorName, $query);

        $data = $response->getData();

        $expectedArray = [
            '_type' => '.percolator',
            '_index' => $index->getName(),
            '_id' => $percolatorName,
            '_version' => 1,
            'created' => true,
            '_shards' => [
                'total' => 1,
                'successful' => 1,
                'failed' => 0,
            ],
        ];

        $this->assertEquals($expectedArray, $data);

        $index->delete();
    }

    /**
     * @group functional
     */
    public function testMatchDoc()
    {
        $index = $this->_createIndex();

        $percolator = new Percolator($index);

        $percolatorName = $index->getName();

        $query = new Term(['name' => 'ruflin']);
        $response = $percolator->registerQuery($percolatorName, $query);

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $doc1 = new Document();
        $doc1->set('name', 'ruflin');

        $doc2 = new Document();
        $doc2->set('name', 'nicolas');

        $index->refresh();

        $matches1 = $percolator->matchDoc($doc1);

        $this->assertCount(1, $matches1);
        $firstPercolatorFound = false;
        foreach ($matches1 as $match) {
            if ($match['_id'] == $percolatorName) {
                $firstPercolatorFound = true;
            }
        }
        $this->assertTrue($firstPercolatorFound);

        $matches2 = $percolator->matchDoc($doc2);
        $this->assertEmpty($matches2);

        $index->delete();
    }

    /**
     * Test case for using filtered percolator queries based on the Elasticsearch documentation examples.
     *
     * @group functional
     */
    public function testFilteredMatchDoc()
    {
        // step one: register create index and setup the percolator query from the ES documentation.
        $index = $this->_createIndex();
        $percolator = new Percolator($index);
        $baseQuery = new Term(['field1' => 'value1']);
        $fields = ['color' => 'blue'];

        $response = $percolator->registerQuery('kuku', $baseQuery, $fields);

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        // refreshing is required in order to ensure the query is really ready for execution.
        $index->refresh();

        // step two: match a document which should match the kuku query when filtered on the blue color
        $doc = new Document();
        $doc->set('field1', 'value1');

        $matches = $percolator->matchDoc($doc, new Term(['color' => 'blue']));
        $this->assertCount(1, $matches, 'No or too much registered query matched.');
        $this->assertEquals('kuku', $matches[0]['_id'], 'A wrong registered query has matched.');

        // step three: validate that using a different color, no registered query matches.
        $matches = $percolator->matchDoc($doc, new Term(['color' => 'green']));
        $this->assertCount(0, $matches, 'A registered query matched, although nothing should match at all.');

        $index->delete();
    }

    /**
     * Test case for using filtered percolator queries based on the Elasticsearch documentation examples.
     *
     * @group functional
     */
    public function testRegisterAndUnregisterPercolator()
    {
        // step one: register create index and setup the percolator query from the ES documentation.
        $index = $this->_createIndex();
        $percolator = new Percolator($index);
        $baseQuery = new Term(['field1' => 'value1']);
        $fields = ['color' => 'blue'];

        $response = $percolator->registerQuery('kuku', $baseQuery, $fields);

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        // refreshing is required in order to ensure the query is really ready for execution.
        $index->refresh();

        // step two: match a document which should match the kuku query when filtered on the blue color
        $doc = new Document();
        $doc->set('field1', 'value1');

        $matches = $percolator->matchDoc($doc, new Term(['color' => 'blue']));
        $this->assertCount(1, $matches, 'No or too much registered query matched.');
        $this->assertEquals('kuku', $matches[0]['_id'], 'A wrong registered query has matched.');

        // step three: validate that using a different color, no registered query matches.
        $matches = $percolator->matchDoc($doc, new Term(['color' => 'green']));
        $this->assertCount(0, $matches, 'A registered query matched, although nothing should match at all.');

        // unregister percolator query
        $response = $percolator->unregisterQuery('kuku');

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        // refreshing is required in order to ensure the query is really ready for execution.
        $index->refresh();

        $matches = $percolator->matchDoc($doc, new Term(['color' => 'blue']));
        $this->assertCount(0, $matches, 'Percolator query did not get deleted.');

        $index->delete();
    }

    protected function _getDefaultPercolator($percolatorName = 'existingDoc')
    {
        $index = $this->_createIndex();
        $percolator = new Percolator($index);

        $query = new Term(['name' => 'foobar']);
        $percolator->registerQuery($percolatorName, $query, ['field1' => ['tag1', 'tag2']]);

        return $percolator;
    }

    protected function _addDefaultDocuments($index, $type = 'testing')
    {
        $type = $index->getType('testing');
        $type->addDocuments([
            new Document(1, ['name' => 'foobar']),
            new Document(2, ['name' => 'barbaz']),
        ]);
        $index->refresh();

        return $type;
    }

    /**
     * @group functional
     */
    public function testPercolateExistingDocWithoutAnyParameter()
    {
        $percolator = $this->_getDefaultPercolator();
        $index = $percolator->getIndex();
        $type = $this->_addDefaultDocuments($index);

        $matches = $percolator->matchExistingDoc(1, $type->getName());

        $this->assertCount(1, $matches);
        $this->assertEquals('existingDoc', $matches[0]['_id']);
        $index->delete();
    }

    /**
     * @group functional
     */
    public function testPercolateExistingDocWithPercolateFormatIds()
    {
        $percolator = $this->_getDefaultPercolator();
        $index = $percolator->getIndex();
        $type = $this->_addDefaultDocuments($index);

        $parameter = ['percolate_format' => 'ids'];
        $matches = $percolator->matchExistingDoc(1, $type->getName(), null, $parameter);

        $this->assertCount(1, $matches);
        $this->assertEquals('existingDoc', $matches[0]);
        $index->delete();
    }

    /**
     * @group functional
     */
    public function testPercolateExistingDocWithIdThatShouldBeUrlEncoded()
    {
        $percolator = $this->_getDefaultPercolator();
        $index = $percolator->getIndex();
        $type = $this->_addDefaultDocuments($index);

        // id with whitespace, should be urlencoded
        $id = 'foo bar 1';

        $type->addDocument(new Document($id, ['name' => 'foobar']));
        $index->refresh();

        $matches = $percolator->matchExistingDoc($id, $type->getName());

        $this->assertCount(1, $matches);
        $index->delete();
    }

    /**
     * @group functional
     */
    public function testPercolateWithAdditionalRequestBodyOptions()
    {
        $index = $this->_createIndex();
        $percolator = new Percolator($index);

        $query = new Term(['name' => 'foo']);
        $response = $percolator->registerQuery('percotest', $query, ['field1' => ['tag1', 'tag2']]);

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $query = new Term(['name' => 'foo']);
        $response = $percolator->registerQuery('percotest1', $query, ['field1' => ['tag2']]);

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $doc1 = new Document();
        $doc1->set('name', 'foo');

        $index->refresh();

        $options = [
            'track_scores' => true,
            'sort' => ['_score' => 'desc'],
            'size' => 1,
        ];

        $matches = $percolator->matchDoc($doc1, new Term(['field1' => 'tag2']), 'type', $options);

        $this->assertCount(1, $matches);
        $this->assertEquals('percotest1', $matches[0]['_id']);
        $this->assertArrayHasKey('_score', $matches[0]);
    }

    /**
     * @group functional
     */
    public function testPercolateExistingDocWithAdditionalRequestBodyOptions()
    {
        $percolatorName = 'existingDoc';
        $percolator = $this->_getDefaultPercolator($percolatorName);

        $query = new Term(['name' => 'foobar']);
        $percolator->registerQuery($percolatorName.'1', $query, ['field1' => ['tag2']]);

        $index = $percolator->getIndex();
        $type = $this->_addDefaultDocuments($index);

        $options = [
            'track_scores' => true,
            'sort' => ['_score' => 'desc'],
            'size' => 1,
        ];

        $matches = $percolator->matchExistingDoc(1, $type->getName(), new Term(['field1' => 'tag2']), $options);

        $this->assertCount(1, $matches);
        $this->assertEquals('existingDoc1', $matches[0]['_id']);
        $this->assertArrayHasKey('_score', $matches[0]);
        $index->delete();
    }

    protected function _createIndex($name = null, $delete = true, $shards = 1)
    {
        $index = parent::_createIndex($name, $delete, $shards);
        $type = $index->getType('.percolator');

        $mapping = new Type\Mapping($type,
            [
                'name' => ['type' => 'string'],
                'field1' => ['type' => 'string'],
            ]
        );
        $mapping->disableSource();

        $type->setMapping($mapping);

        return $index;
    }
}
