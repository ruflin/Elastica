<?php

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Query;
use Elastica\Script;
use Elastica\ScriptFields;
use Elastica\Test\Base as BaseTest;

class ScriptFieldsTest extends BaseTest
{
    protected $index;

    public function setUp()
    {
        $this->index = $this->_createIndex();
    }

    public function testNewScriptFields()
    {
        $script = new Script('1 + 2');

        // addScript
        $scriptFields = new ScriptFields();
        $scriptFields->addScript('test', $script);
        $this->assertEquals($scriptFields->getParam('test'), $script->toArray());

        // setScripts
        $scriptFields = new ScriptFields();
        $scriptFields->setScripts(array(
            'test' => $script,
        ));
        $this->assertEquals($scriptFields->getParam('test'), $script->toArray());

        // Constructor
        $scriptFields = new ScriptFields(array(
            'test' => $script,
        ));
        $this->assertEquals($scriptFields->getParam('test'), $script->toArray());
    }

    public function testSetScriptFields()
    {
        $query = new Query();
        $script = new Script('1 + 2');

        $scriptFields = new ScriptFields(array(
            'test' => $script,
        ));
        $query->setScriptFields($scriptFields);
        $this->assertEquals($query->getParam('script_fields'), $scriptFields->toArray());

        $query->setScriptFields(array(
            'test' => $script,
        ));
        $this->assertEquals($query->getParam('script_fields'), $scriptFields->toArray());
    }

    /**
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testNameException()
    {
        $script = new Script('1 + 2');
        $scriptFields = new ScriptFields(array($script));
    }

    public function testQuery()
    {
        $type = $this->index->getType('test');

        $doc = new Document(1, array('firstname' => 'guschti', 'lastname' => 'ruflin'));
        $type->addDocument($doc);
        $this->index->refresh();

        $query = new Query();
        $script = new Script('1 + 2');
        $scriptFields = new ScriptFields(array(
            'test' => $script,
        ));
        $query->setScriptFields($scriptFields);

        $resultSet = $type->search($query);
        $first = $resultSet->current()->getData();

        // 1 + 2
        $this->assertEquals(3, $first['test'][0]);
    }
}
