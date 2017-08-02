<?php
namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\QueryString;
use Elastica\Test\Base as BaseTest;

class QueryStringTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testSearchMultipleFields()
    {
        $str = md5(rand());
        $query = new QueryString($str);

        $expected = array(
            'query' => $str,
        );

        $this->assertEquals(array('query_string' => $expected), $query->toArray());

        $fields = array();
        $max = rand() % 10 + 1;
        for ($i = 0; $i <  $max; ++$i) {
            $fields[] = md5(rand());
        }

        $query->setFields($fields);
        $expected['fields'] = $fields;
        $this->assertEquals(array('query_string' => $expected), $query->toArray());

        foreach (array(false, true) as $val) {
            $query->setUseDisMax($val);
            $expected['use_dis_max'] = $val;

            $this->assertEquals(array('query_string' => $expected), $query->toArray());
        }
    }

    /**
     * @group functional
     */
    public function testSearch()
    {
        $index = $this->_createIndex();
        $index->getSettings()->setNumberOfReplicas(0);
        $type = $index->getType('helloworld');

        $doc = new Document(1, array('email' => 'test@test.com', 'username' => 'hanswurst', 'test' => array('2', '3', '5')));
        $type->addDocument($doc);
        $index->refresh();

        $queryString = new QueryString('test*');
        $resultSet = $type->search($queryString);

        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * Tests if search in multiple fields is possible.
     *
     * @group functional
     */
    public function testSearchFields()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $doc = new Document(1, array('title' => 'hello world', 'firstname' => 'nicolas', 'lastname' => 'ruflin', 'price' => '102', 'year' => '2012'));
        $type->addDocument($doc);
        $index->refresh();

        $query = new QueryString();
        $query = $query->setQuery('ruf*');
        $query = $query->setDefaultField('title');
        $query = $query->setFields(array('title', 'firstname', 'lastname', 'price', 'year'));

        $resultSet = $type->search($query);
        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * @group unit
     */
    public function testSetDefaultOperator()
    {
        $operator = 'AND';
        $query = new QueryString('test');
        $query->setDefaultOperator($operator);

        $data = $query->toArray();

        $this->assertEquals($data['query_string']['default_operator'], $operator);
    }

    /**
     * @group unit
     */
    public function testSetDefaultField()
    {
        $default = 'field1';
        $query = new QueryString('test');
        $query->setDefaultField($default);

        $data = $query->toArray();

        $this->assertEquals($data['query_string']['default_field'], $default);
    }

    /**
     * @group unit
     */
    public function testSetRewrite()
    {
        $rewrite = 'scoring_boolean';
        $query = new QueryString('test');
        $query->setRewrite($rewrite);

        $data = $query->toArray();

        $this->assertEquals($data['query_string']['rewrite'], $rewrite);
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testSetQueryInvalid()
    {
        $query = new QueryString();
        $query->setQuery(array());
    }

    /**
     * @group unit
     */
    public function testSetTimezone()
    {
        $timezone = 'Europe/Paris';
        $text = 'date:[2012 TO 2014]';

        $query = new QueryString($text);
        $query->setTimezone($timezone);

        $expected = array(
            'query_string' => array(
                'query' => $text,
                'time_zone' => $timezone,
            ),
        );

        $this->assertEquals($expected, $query->toArray());
        $this->assertInstanceOf('Elastica\Query\QueryString', $query->setTimezone($timezone));
    }

    /**
     * @group unit
     */
    public function testSetPhraseSlop()
    {
        $phraseSlop = 9;

        $query = new QueryString('test');
        $query->setPhraseSlop($phraseSlop);

        $data = $query->toArray();
        $this->assertEquals($phraseSlop, $data['query_string']['phrase_slop']);
    }

    /**
     * @group functional
     */
    public function testSetBoost()
    {
        $index = $this->_createIndex();
        $query = new QueryString('test');
        $query->setBoost(9.3);

        $doc = new Document('', array('name' => 'test'));
        $index->getType('test')->addDocument($doc);
        $index->refresh();

        $resultSet = $index->search($query);

        $this->assertEquals(1, $resultSet->count());
    }
}
