<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastica\Document;
use Elastica\Query\QueryString;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class QueryStringTest extends BaseTest
{
    #[Group('unit')]
    public function testSearchMultipleFields(): void
    {
        $str = \uniqid();
        $query = new QueryString($str);

        $expected = [
            'query' => $str,
        ];

        $this->assertEquals(['query_string' => $expected], $query->toArray());

        $fields = [];
        $max = \random_int(1, 10);
        for ($i = 0; $i < $max; ++$i) {
            $fields[] = \uniqid();
        }

        $query->setFields($fields);
        $expected['fields'] = $fields;
        $this->assertEquals(['query_string' => $expected], $query->toArray());

        foreach ([false, true] as $val) {
            $query->setUseDisMax($val);
            $expected['use_dis_max'] = $val;

            $this->assertEquals(['query_string' => $expected], $query->toArray());
        }
    }

    #[Group('functional')]
    public function testSearch(): void
    {
        $index = $this->_createIndex();
        $index->getSettings()->setNumberOfReplicas(0);

        $doc = new Document('1', ['email' => 'test@test.com', 'username' => 'hanswurst', 'test' => ['2', '3', '5']]);
        $index->addDocument($doc);
        $index->refresh();

        $queryString = new QueryString('test*');
        $resultSet = $index->search($queryString);

        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * Tests if search in multiple fields is possible.
     */
    #[Group('functional')]
    public function testSearchFields(): void
    {
        $index = $this->_createIndex();

        $doc = new Document('1', ['title' => 'hello world', 'firstname' => 'nicolas', 'lastname' => 'ruflin', 'price' => '102', 'year' => '2012']);
        $index->addDocument($doc);
        $index->refresh();

        $query = new QueryString();
        $query = $query->setQuery('ruf*');
        $query = $query->setFields(['title', 'firstname', 'lastname', 'price', 'year']);

        $resultSet = $index->search($query);
        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * Tests if search in multiple fields is possible.
     */
    #[Group('functional')]
    public function testSearchFieldsValidationException(): void
    {
        $index = $this->_createIndex();

        $doc = new Document('1', ['title' => 'hello world', 'firstname' => 'nicolas', 'lastname' => 'ruflin', 'price' => '102', 'year' => '2012']);
        $index->addDocument($doc);
        $index->refresh();

        $query = new QueryString();
        $query = $query->setQuery('ruf*');
        $query = $query->setDefaultField('title');
        $query = $query->setFields(['title', 'firstname', 'lastname', 'price', 'year']);

        try {
            $index->search($query);
        } catch (ClientResponseException $e) {
            $error = \json_decode((string) $e->getResponse()->getBody(), true)['error'] ?? null;

            $this->assertSame('query_shard_exception', $error['root_cause'][0]['type']);
            $this->assertStringContainsString('failed to create query', $error['root_cause'][0]['reason']);
            $this->assertStringContainsString('[fields] parameter in conjunction with [default_field]', $error['failed_shards'][0]['reason']['caused_by']['reason']);

            $this->assertEquals(400, $e->getResponse()->getStatusCode());
        }
    }

    #[Group('unit')]
    public function testSetDefaultOperator(): void
    {
        $operator = 'AND';
        $query = new QueryString('test');
        $query->setDefaultOperator($operator);

        $data = $query->toArray();

        $this->assertEquals($data['query_string']['default_operator'], $operator);
    }

    #[Group('unit')]
    public function testSetDefaultField(): void
    {
        $default = 'field1';
        $query = new QueryString('test');
        $query->setDefaultField($default);

        $data = $query->toArray();

        $this->assertEquals($data['query_string']['default_field'], $default);
    }

    #[Group('unit')]
    public function testSetAnalyzer(): void
    {
        $value = 'test';
        $query = new QueryString('test');
        $query->setAnalyzer($value);

        $this->assertEquals($value, $query->toArray()['query_string']['analyzer']);
    }

    #[Group('unit')]
    public function testSetAllowLeadingWildcard(): void
    {
        $value = true;
        $query = new QueryString('test');
        $query->setAllowLeadingWildcard($value);

        $this->assertEquals($value, $query->toArray()['query_string']['allow_leading_wildcard']);
    }

    #[Group('unit')]
    public function testSetEnablePositionIncrements(): void
    {
        $value = true;
        $query = new QueryString('test');
        $query->setEnablePositionIncrements($value);

        $this->assertEquals($value, $query->toArray()['query_string']['enable_position_increments']);
    }

    #[Group('unit')]
    public function testSetFuzzyPrefixLength(): void
    {
        $value = 1;
        $query = new QueryString('test');
        $query->setFuzzyPrefixLength($value);

        $this->assertEquals($value, $query->toArray()['query_string']['fuzzy_prefix_length']);
    }

    #[Group('unit')]
    public function testSetFuzzyMinSim(): void
    {
        $value = 0.1;
        $query = new QueryString('test');
        $query->setFuzzyMinSim($value);

        $this->assertEquals($value, $query->toArray()['query_string']['fuzzy_min_sim']);
    }

    #[Group('unit')]
    public function testSetAnalyzeWildcard(): void
    {
        $value = true;
        $query = new QueryString('test');
        $query->setAnalyzeWildcard($value);

        $this->assertEquals($value, $query->toArray()['query_string']['analyze_wildcard']);
    }

    #[Group('unit')]
    public function testSetTieBreaker(): void
    {
        $value = 0.2;
        $query = new QueryString('test');
        $query->setTieBreaker($value);

        $this->assertEquals($value, $query->toArray()['query_string']['tie_breaker']);
    }

    #[Group('unit')]
    public function testSetRewrite(): void
    {
        $rewrite = 'scoring_boolean';
        $query = new QueryString('test');
        $query->setRewrite($rewrite);

        $data = $query->toArray();

        $this->assertEquals($data['query_string']['rewrite'], $rewrite);
    }

    #[Group('unit')]
    public function testSetTimezone(): void
    {
        $timezone = 'Europe/Paris';
        $text = 'date:[2012 TO 2014]';

        $query = (new QueryString($text))
            ->setTimezone($timezone)
        ;

        $expected = [
            'query_string' => [
                'query' => $text,
                'time_zone' => $timezone,
            ],
        ];

        $this->assertSame($expected, $query->toArray());
    }

    #[Group('unit')]
    public function testSetPhraseSlop(): void
    {
        $phraseSlop = 9;

        $query = new QueryString('test');
        $query->setPhraseSlop($phraseSlop);

        $data = $query->toArray();
        $this->assertEquals($phraseSlop, $data['query_string']['phrase_slop']);
    }

    #[Group('functional')]
    public function testSetBoost(): void
    {
        $index = $this->_createIndex();
        $query = new QueryString('test');
        $query->setBoost(9.3);

        $doc = new Document('', ['name' => 'test']);
        $index->addDocument($doc);
        $index->refresh();

        $resultSet = $index->search($query);

        $this->assertEquals(1, $resultSet->count());
    }
}
