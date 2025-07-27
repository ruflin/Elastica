<?php

declare(strict_types=1);

namespace Elastica\Test\Script;

use Elastica\Mapping;
use Elastica\Query;
use Elastica\Script\Script;
use Elastica\Script\ScriptFields;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class ScriptFieldsTest extends BaseTest
{
    #[Group('unit')]
    public function testNewScriptFields(): void
    {
        $script = new Script('1 + 2');

        // addScript
        $scriptFields = new ScriptFields();
        $scriptFields->addScript('test', $script);
        $this->assertSame($script, $scriptFields->getParam('test'));

        // setScripts
        $scriptFields = new ScriptFields();
        $scriptFields->setScripts([
            'test' => $script,
        ]);
        $this->assertSame($script, $scriptFields->getParam('test'));

        // Constructor
        $scriptFields = new ScriptFields([
            'test' => $script,
        ]);
        $this->assertSame($script, $scriptFields->getParam('test'));
    }

    #[Group('unit')]
    public function testSetScriptFields(): void
    {
        $query = new Query();
        $script = new Script('1 + 2');

        $scriptFields = new ScriptFields([
            'test' => $script,
        ]);
        $query->setScriptFields($scriptFields);
        $this->assertSame($scriptFields, $query->getParam('script_fields'));

        $query->setScriptFields([
            'test' => $script,
        ]);
        $this->assertSame($script, $query->getParam('script_fields')->getParam('test'));
    }

    #[Group('functional')]
    public function testQuery(): void
    {
        $index = $this->_createIndex();

        $doc = $index->createDocument('1', ['firstname' => 'guschti', 'lastname' => 'ruflin']);
        $index->addDocument($doc);
        $index->refresh();

        $query = new Query();
        $script = new Script('1 + 2');
        $scriptFields = new ScriptFields([
            'test' => $script,
        ]);
        $query->setScriptFields($scriptFields);

        $resultSet = $index->search($query);
        $first = $resultSet->current()->getData();

        // 1 + 2
        $this->assertEquals(3, $first['test'][0]);
    }

    #[Group('functional')]
    public function testScriptFieldWithJoin(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('testscriptfieldwithjoin');
        $index->create([], [
            'recreate' => true,
        ]);

        $mapping = new Mapping([
            'text' => ['type' => 'keyword'],
            'name' => ['type' => 'keyword'],
            'my_join_field' => [
                'type' => 'join',
                'relations' => [
                    'question' => 'answer',
                ],
            ],
        ]);

        $index->setMapping($mapping);
        $index->refresh();

        $doc1 = $index->createDocument('1', [
            'text' => 'this is the 1st question',
            'my_join_field' => [
                'name' => 'question',
            ],
        ]);
        $doc2 = $index->createDocument('2', [
            'text' => 'this is the 2nd question',
            'my_join_field' => [
                'name' => 'question',
            ],
        ]);
        $index->addDocuments([$doc1, $doc2]);

        $doc3 = $index->createDocument('3', [
            'text' => 'this is an answer, the 1st',
            'name' => 'rico',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 1,
            ],
        ]);
        $doc4 = $index->createDocument('4', [
            'text' => 'this is an answer, the 2nd',
            'name' => 'fede',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 2,
            ],
        ]);
        $doc5 = $index->createDocument('5', [
            'text' => 'this is an answer, the 3rd',
            'name' => 'fede',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 2,
            ],
        ]);

        $this->_getClient()->addDocuments([$doc3, $doc4, $doc5], ['routing' => 1]);
        $index->refresh();

        $query = new Query();
        $script = new Script("doc['my_join_field#question']");
        $scriptFields = new ScriptFields([
            'text' => $script,
        ]);
        $query->setScriptFields($scriptFields);
        $resultSet = $index->search($query);
        $results = $resultSet->getResults();

        $this->assertEquals(1, $results[0]->getHit()['fields']['text'][0]);
        $this->assertEquals(2, $results[1]->getHit()['fields']['text'][0]);
    }
}
