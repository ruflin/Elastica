<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\Match;
use Elastica\Query\MatchPhrase;
use Elastica\Query\MatchPhrasePrefix;
use Elastica\Test\Base as BaseTest;

class MatchTest extends BaseTest
{
    public function testToArray()
    {
        $field = 'test';
        $testQuery = 'Nicolas Ruflin';
        $type = 'phrase';
        $operator = 'and';
        $analyzer = 'myanalyzer';
        $boost = 2.0;
        $minimumShouldMatch = 2;
        $fuzziness = 0.3;
        $fuzzyRewrite = 'constant_score_boolean';
        $prefixLength = 3;
        $maxExpansions = 12;

        $query = new Match();
        $query->setFieldQuery($field, $testQuery);
        $query->setFieldType($field, $type);
        $query->setFieldOperator($field, $operator);
        $query->setFieldAnalyzer($field, $analyzer);
        $query->setFieldBoost($field, $boost);
        $query->setFieldMinimumShouldMatch($field, $minimumShouldMatch);
        $query->setFieldFuzziness($field, $fuzziness);
        $query->setFieldFuzzyRewrite($field, $fuzzyRewrite);
        $query->setFieldPrefixLength($field, $prefixLength);
        $query->setFieldMaxExpansions($field, $maxExpansions);

        $expectedArray = array(
            'match' => array(
                $field => array(
                    'query' => $testQuery,
                    'type' => $type,
                    'operator' => $operator,
                    'analyzer' => $analyzer,
                    'boost' => $boost,
                    'minimum_should_match' => $minimumShouldMatch,
                    'fuzziness' => $fuzziness,
                    'fuzzy_rewrite' => $fuzzyRewrite,
                    'prefix_length' => $prefixLength,
                    'max_expansions' => $maxExpansions,
                ),
            ),
        );

        $this->assertEquals($expectedArray, $query->toArray());
    }

    public function testMatch()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('test');

        $doc = new Document(1, array('name' => 'Basel-Stadt'));
        $type->addDocument($doc);
        $doc = new Document(2, array('name' => 'New York'));
        $type->addDocument($doc);
        $doc = new Document(3, array('name' => 'New Hampshire'));
        $type->addDocument($doc);
        $doc = new Document(4, array('name' => 'Basel Land'));
        $type->addDocument($doc);

        $index->refresh();

        $field = 'name';
        $operator = 'or';

        $query = new Match();
        $query->setFieldQuery($field, 'Basel New');
        $query->setFieldOperator($field, $operator);

        $resultSet = $index->search($query);

        $this->assertEquals(4, $resultSet->count());
    }

    public function testMatchZeroTerm()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('test');
        $doc = new Document(1, array('name' => 'Basel-Stadt'));
        $type->addDocument($doc);
        $doc = new Document(2, array('name' => 'New York'));
        $type->addDocument($doc);
        $index->refresh();

        $query = new Match();
        $query->setFieldQuery('name', '');
        $query->setFieldZeroTermsQuery('name', Match::ZERO_TERM_ALL);

        $resultSet = $index->search($query);

        $this->assertEquals(2, $resultSet->count());
    }

    public function testMatchPhrase()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('test');

        $doc = new Document(1, array('name' => 'Basel-Stadt'));
        $type->addDocument($doc);
        $doc = new Document(2, array('name' => 'New York'));
        $type->addDocument($doc);
        $doc = new Document(3, array('name' => 'New Hampshire'));
        $type->addDocument($doc);
        $doc = new Document(4, array('name' => 'Basel Land'));
        $type->addDocument($doc);

        $index->refresh();

        $field = 'name';
        $type = 'phrase';

        $query = new Match();
        $query->setFieldQuery($field, 'New York');
        $query->setFieldType($field, $type);

        $resultSet = $index->search($query);

        $this->assertEquals(1, $resultSet->count());
    }

    public function testMatchPhraseAlias()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('test');

        $doc = new Document(1, array('name' => 'Basel-Stadt'));
        $type->addDocument($doc);
        $doc = new Document(2, array('name' => 'New York'));
        $type->addDocument($doc);
        $doc = new Document(3, array('name' => 'New Hampshire'));
        $type->addDocument($doc);
        $doc = new Document(4, array('name' => 'Basel Land'));
        $type->addDocument($doc);

        $index->refresh();

        $field = 'name';

        $query = new MatchPhrase();
        $query->setFieldQuery($field, 'New York');

        $resultSet = $index->search($query);

        $this->assertEquals(1, $resultSet->count());
    }

    public function testMatchPhrasePrefix()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('test');

        $doc = new Document(1, array('name' => 'Basel-Stadt'));
        $type->addDocument($doc);
        $doc = new Document(2, array('name' => 'New York'));
        $type->addDocument($doc);
        $doc = new Document(3, array('name' => 'New Hampshire'));
        $type->addDocument($doc);
        $doc = new Document(4, array('name' => 'Basel Land'));
        $type->addDocument($doc);

        $index->refresh();

        $field = 'name';
        $type = 'phrase_prefix';

        $query = new Match();
        $query->setFieldQuery($field, 'New');
        $query->setFieldType($field, $type);

        $resultSet = $index->search($query);

        $this->assertEquals(2, $resultSet->count());
    }

    public function testMatchPhrasePrefixAlias()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('test');

        $doc = new Document(1, array('name' => 'Basel-Stadt'));
        $type->addDocument($doc);
        $doc = new Document(2, array('name' => 'New York'));
        $type->addDocument($doc);
        $doc = new Document(3, array('name' => 'New Hampshire'));
        $type->addDocument($doc);
        $doc = new Document(4, array('name' => 'Basel Land'));
        $type->addDocument($doc);

        $index->refresh();

        $field = 'name';

        $query = new MatchPhrasePrefix();
        $query->setFieldQuery($field, 'New');

        $resultSet = $index->search($query);

        $this->assertEquals(2, $resultSet->count());
    }


    public function testMatchFuzzinessType()
    {
        $field = 'test';
        $query = new Match();

        $fuzziness = "AUTO";
        $query->setFieldFuzziness($field, $fuzziness);

        $parameters =  $query->getParam($field);
        $this->assertEquals($fuzziness, $parameters['fuzziness']);

        $fuzziness = 0.3;
        $query->setFieldFuzziness($field, $fuzziness);

        $parameters =  $query->getParam($field);
        $this->assertEquals($fuzziness, $parameters['fuzziness']);
    }
}
