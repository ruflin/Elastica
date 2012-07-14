<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

class Elastica_ScriptTest extends Elastica_Test
{
    protected $index;

    public function setUp()
    {
        $this->index = $this->_createIndex();
    }

    public function tearDown()
    {
        $this->index->delete();
    }

    public function testAddScript()
    {
        $query = new Elastica_Query();
        $script = new Elastica_Script('1 + 1');
        $query->addScriptField('test', $script);

        $this->assertEquals($query->getParam('script_fields'), array('test' => $script->toArray()));
    }

    public function testSetScript()
    {
        $query = new Elastica_Query();
        $script = new Elastica_Script('1 + 1');
        $query->setScriptFields(array(
            'test' => $script
        ));

        $this->assertEquals($query->getParam('script_fields'), array('test' => $script->toArray()));
    }

    public function testQuery()
    {
        $type = $this->index->getType('test');

        $doc = new Elastica_Document(1, array('firstname' => 'guschti', 'lastname' => 'ruflin'));
        $type->addDocument($doc);
        $this->index->refresh();

        $query = new Elastica_Query();
        $script = new Elastica_Script('1 + x');
        $script->setParams(array('x' => 2));
        $query->addScriptField('test', $script);

        $resultSet = $type->search($query);
        $first = $resultSet->current()->getData();

        // 1 + 2
        $this->assertEquals(3, $first['test']);
    }
}
