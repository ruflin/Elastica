<?php

namespace Elastica\Test\Suggest;

use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use Elastica\Suggest\Completion;
use Elastica\Test\Base as BaseTest;

class CompletionTest extends BaseTest
{
    /**
     * @return Index
     */
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();
        $type = $index->getType('song');

        $type->setMapping(array(
            'fieldName' => array(
                'type' => 'completion',
                'payloads' => true,
            ),
        ));

        $type->addDocuments(array(
            new Document(1, array(
                'fieldName' => array(
                    'input' => array('Nevermind', 'Nirvana'),
                    'output' => 'Nevermind - Nirvana',
                    'payload' => array(
                        'year' => 1991,
                    ),
                ),
            )),
            new Document(2, array(
                'fieldName' => array(
                    'input' => array('Bleach', 'Nirvana'),
                    'output' => 'Bleach - Nirvana',
                    'payload' => array(
                        'year' => 1989,
                    ),
                ),
            )),
            new Document(3, array(
                'fieldName' => array(
                    'input' => array('Incesticide', 'Nirvana'),
                    'output' => 'Incesticide - Nirvana',
                    'payload' => array(
                        'year' => 1992,
                    ),
                ),
            )),
        ));

        $index->refresh();

        return $index;
    }

    /**
     * @group unit
     */
    public function testToArray()
    {
        $suggest = new Completion('suggestName', 'fieldName');
        $suggest->setText('foo');
        $suggest->setSize(10);
        $expected = array(
            'text' => 'foo',
            'completion' => array(
                'size' => 10,
                'field' => 'fieldName',
            ),
        );
        $this->assertEquals($expected, $suggest->toArray());
    }

    /**
     * @group functional
     */
    public function testSuggestWorks()
    {
        $suggest = new Completion('suggestName', 'fieldName');
        $suggest->setText('Never');

        $index = $this->_getIndexForTest();
        $resultSet = $index->search(Query::create($suggest));

        $this->assertTrue($resultSet->hasSuggests());

        $suggests = $resultSet->getSuggests();
        $options = $suggests['suggestName'][0]['options'];

        $this->assertCount(1, $options);
        $this->assertEquals('Nevermind - Nirvana', $options[0]['text']);
        $this->assertEquals(1991, $options[0]['payload']['year']);
    }

    /**
     * @group functional
     */
    public function testFuzzySuggestWorks()
    {
        $suggest = new Completion('suggestName', 'fieldName');
        $suggest->setFuzzy(array('fuzziness' => 2));
        $suggest->setText('Neavermint');

        $index = $this->_getIndexForTest();
        $resultSet = $index->search(Query::create($suggest));

        $this->assertTrue($resultSet->hasSuggests());

        $suggests = $resultSet->getSuggests();
        $options = $suggests['suggestName'][0]['options'];

        $this->assertCount(1, $options);
        $this->assertEquals('Nevermind - Nirvana', $options[0]['text']);
    }

    /**
     * @group unit
     */
    public function testSetFuzzy()
    {
        $suggest = new Completion('suggestName', 'fieldName');

        $fuzzy = array(
            'unicode_aware' => true,
            'fuzziness' => 3,
        );

        $suggest->setFuzzy($fuzzy);

        $this->assertEquals($fuzzy, $suggest->getParam('fuzzy'));

        $this->assertInstanceOf('Elastica\\Suggest\\Completion', $suggest->setFuzzy($fuzzy));
    }
}
