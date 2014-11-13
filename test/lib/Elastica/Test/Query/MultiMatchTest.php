<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use Elastica\Query\MultiMatch;
use Elastica\Test\Base as BaseTest;
use Elastica\Type;
use Elastica\Type\Mapping;

class MultiMatchTest extends BaseTest
{
    private $index;
    private $multiMatch;

    private static $data = array(
        array('id' => 1, 'name' => 'Rodolfo', 'last_name' => 'Moraes',   'full_name' => 'Rodolfo Moraes'),
        array('id' => 2, 'name' => 'Tristan', 'last_name' => 'Maindron', 'full_name' => 'Tristan Maindron'),
        array('id' => 3, 'name' => 'Monique', 'last_name' => 'Maindron', 'full_name' => 'Monique Maindron'),
        array('id' => 4, 'name' => 'John',    'last_name' => 'not Doe',  'full_name' => 'John not Doe'),
    );

    protected function setUp()
    {
        $this->index = $this->_generateIndex();
        $this->multiMatch = new MultiMatch();
    }

    public function testMinimumShouldMatch()
    {
        $this->multiMatch->setQuery('Tristan Maindron');
        $this->multiMatch->setFields(array('full_name', 'name'));
        $this->multiMatch->setMinimumShouldMatch(2);
        $resultSet = $this->_getResults();

        $this->assertEquals(1, $resultSet->count());
    }

    public function testAndOperator()
    {
        $this->multiMatch->setQuery('Monique Maindron');
        $this->multiMatch->setFields(array('full_name', 'name'));
        $this->multiMatch->setOperator(MultiMatch::OPERATOR_AND);
        $resultSet = $this->_getResults();

        $this->assertEquals(1, $resultSet->count());
    }

    public function testType()
    {
        $this->multiMatch->setQuery('Trist');
        $this->multiMatch->setFields(array('full_name', 'name'));
        $this->multiMatch->setType(MultiMatch::TYPE_PHRASE_PREFIX);
        $resultSet = $this->_getResults();

        $this->assertEquals(1, $resultSet->count());
    }

    public function testFuzzy()
    {
        $this->multiMatch->setQuery('Tritsan'); // Mispell on purpose
        $this->multiMatch->setFields(array('full_name', 'name'));
        $this->multiMatch->setFuzziness(2);
        $resultSet = $this->_getResults();

        $this->assertEquals(1, $resultSet->count());

        $this->multiMatch->setQuery('Tritsan'); // Mispell on purpose
        $this->multiMatch->setFields(array('full_name', 'name'));
        $this->multiMatch->setFuzziness(0);
        $resultSet = $this->_getResults();

        $this->assertEquals(0, $resultSet->count());
    }

    public function testFuzzyWithOptions1()
    {
        // Here Elasticsearch will not accept mispells
        // on the first 6 letters.
        $this->multiMatch->setQuery('Tritsan'); // Mispell on purpose
        $this->multiMatch->setFields(array('full_name', 'name'));
        $this->multiMatch->setFuzziness(2);
        $this->multiMatch->setPrefixLength(6);
        $resultSet = $this->_getResults();

        $this->assertEquals(0, $resultSet->count());   
    }
    
    public function testFuzzyWithOptions2() {

        // Here with a 'M' search we should hit 'Moraes' first
        // and then stop because MaxExpansion = 1.
        // If MaxExpansion was set to 2, we could hit "Maindron" too.
        $this->multiMatch->setQuery('M');
        $this->multiMatch->setFields(array('name'));
        $this->multiMatch->setType(MultiMatch::TYPE_PHRASE_PREFIX);
        $this->multiMatch->setPrefixLength(0);
        $this->multiMatch->setMaxExpansions(1);
        $resultSet = $this->_getResults();

        $this->assertEquals(1, $resultSet->count());
    }

    public function testZeroTerm()
    {
        $this->multiMatch->setQuery('not'); // This is a stopword.
        $this->multiMatch->setFields(array('full_name', 'last_name'));
        $this->multiMatch->setZeroTermsQuery(MultiMatch::ZERO_TERM_NONE);
        $this->multiMatch->setAnalyzer('stops');
        $resultSet = $this->_getResults();

        $this->assertEquals(0, $resultSet->count());

        $this->multiMatch->setZeroTermsQuery(MultiMatch::ZERO_TERM_ALL);
        $resultSet = $this->_getResults();

        $this->assertEquals(4, $resultSet->count());
    }

    public function testBaseMultiMatch()
    {
        $this->multiMatch->setQuery('Rodolfo');
        $this->multiMatch->setFields(array('name', 'last_name'));
        $resultSet = $this->_getResults();

        $this->assertEquals(1, $resultSet->count());

        $this->multiMatch->setQuery('Moraes');
        $this->multiMatch->setFields(array('name', 'last_name'));
        $resultSet = $this->_getResults();

        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * Executes the query with the current multimatch.
     */
    private function _getResults()
    {
        return $this->index->search(new Query($this->multiMatch));
    }

    /**
     * Builds an index for testing.
     */
    private function _generateIndex()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');

        $index->create(array(
            'analysis' => array(
                'analyzer' => array(
                    'noStops' => array(
                        'type'      => 'standard',
                        'stopwords' => '_none_'
                    ),
                    'stops' => array(
                        'type'      => 'standard',
                        'stopwords' => array('not')
                    ),
                ),
            )
        ), true);

        $type = $index->getType('test');

        $mapping = new Mapping($type, array(
            'name'      => array('type' => 'string', 'store' => 'no', 'analyzer' => 'noStops'),
            'last_name' => array('type' => 'string', 'store' => 'no', 'analyzer' => 'noStops'),
            'full_name' => array('type' => 'string', 'store' => 'no', 'analyzer' => 'noStops'),
        ));

        $type->setMapping($mapping);

        foreach (self::$data as $key => $docData) {
            $type->addDocument(new Document($key, $docData));
        }

        // Refresh index
        $index->refresh();

        return $index;
    }
}
