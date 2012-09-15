<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

class Elastica_ScriptFieldsTest extends Elastica_Test
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

    public function testNewScriptFields()
    {
        $script = new Elastica_Script('1 + 2');

        // addScript
        $scriptFields = new Elastica_ScriptFields;
        $scriptFields->addScript('test', $script);
        $this->assertEquals($scriptFields->getParam('test'), $script->toArray());

        // setScripts
        $scriptFields = new Elastica_ScriptFields;
        $scriptFields->setScripts(array(
            'test' => $script
        ));
        $this->assertEquals($scriptFields->getParam('test'), $script->toArray());

        // Constructor
        $scriptFields = new Elastica_ScriptFields(array(
            'test' => $script
        ));
        $this->assertEquals($scriptFields->getParam('test'), $script->toArray());
    }

    public function testSetScriptFields()
    {
        $query = new Elastica_Query;
        $script = new Elastica_Script('1 + 2');

        $scriptFields = new Elastica_ScriptFields(array(
            'test' => $script
        ));
        $query->setScriptFields($scriptFields);
        $this->assertEquals($query->getParam('script_fields'), $scriptFields->toArray());

        $query->setScriptFields(array(
            'test' => $script
        ));
        $this->assertEquals($query->getParam('script_fields'), $scriptFields->toArray());
    }

    public function testNameException()
    {
        $this->setExpectedException('Elastica_Exception_Invalid');

        $script = new Elastica_Script('1 + 2');
        $scriptFields = new Elastica_ScriptFields(array($script));
    }

    public function testQuery()
    {
        $type = $this->index->getType('test');

        $doc = new Elastica_Document(1, array('firstname' => 'guschti', 'lastname' => 'ruflin'));
        $type->addDocument($doc);
        $this->index->refresh();

        $query = new Elastica_Query();
        $script = new Elastica_Script('1 + 2');
        $scriptFields = new Elastica_ScriptFields(array(
            'test' => $script
        ));
        $query->setScriptFields($scriptFields);

        $resultSet = $type->search($query);
        $first = $resultSet->current()->getData();

        // 1 + 2
        $this->assertEquals(3, $first['test']);
    }
}
