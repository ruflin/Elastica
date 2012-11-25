<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_QueryStringTest extends Elastica_Test
{
    public function testSearchMultipleFields()
    {
        $str = md5(rand());
        $query = new Elastica_Query_QueryString($str);

        $expected = array(
            'query' => $str
        );

        $this->assertEquals(array('query_string' => $expected), $query->toArray());

        $fields = array();
        $max = rand() % 10 + 1;
        for ($i = 0; $i <  $max; $i++) {
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

    public function testSearch()
    {
        $client = new Elastica_Client();
        $index = new Elastica_Index($client, 'test');
        $index->create(array(), true);
        $index->getSettings()->setNumberOfReplicas(0);
        //$index->getSettings()->setNumberOfShards(1);

        $type = new Elastica_Type($index, 'helloworld');

        $doc = new Elastica_Document(1, array('email' => 'test@test.com', 'username' => 'hanswurst', 'test' => array('2', '3', '5')));
        $type->addDocument($doc);

        // Refresh index
        $index->refresh();

        $queryString = new Elastica_Query_QueryString('test*');
        $resultSet = $type->search($queryString);

        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * Tests if search in multiple fields is possible
     */
    public function testSearchFields()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $doc = new Elastica_Document(1, array('title' => 'hello world', 'firstname' => 'nicolas', 'lastname' => 'ruflin', 'price' => '102', 'year' => '2012'));
        $type->addDocument($doc);
        $index->refresh();

        $query = new Elastica_Query_QueryString();
        $query = $query->setQuery('ruf*');
        $query = $query->setDefaultField('title');
        $query = $query->setFields(array('title', 'firstname', 'lastname', 'price', 'year'));

        $resultSet = $type->search($query);
        $this->assertEquals(1, $resultSet->count());
    }

    public function testSetDefaultOperator()
    {
        $operator = 'AND';
        $query = new Elastica_Query_QueryString('test');
        $query->setDefaultOperator($operator);

        $data = $query->toArray();

        $this->assertEquals($data['query_string']['default_operator'], $operator);
    }

    public function testSetDefaultField()
    {
        $default = 'field1';
        $query = new Elastica_Query_QueryString('test');
        $query->setDefaultField($default);

        $data = $query->toArray();

        $this->assertEquals($data['query_string']['default_field'], $default);
    }

    public function testSetRewrite()
    {
            $rewrite = 'scoring_boolean';
            $query = new Elastica_Query_QueryString('test');
            $query->setRewrite($rewrite);

            $data = $query->toArray();

            $this->assertEquals($data['query_string']['rewrite'], $rewrite);
    }

    /**
     * @expectedException Elastica_Exception_Invalid
     */
    public function testSetQueryStringInvalid()
    {
        $query = new Elastica_Query_QueryString();
        $query->setQueryString(array());
    }
}
