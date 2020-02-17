<?php

namespace Elastica\Test\Suggest;

use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;
use Elastica\Suggest;
use Elastica\Suggest\Completion;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class CompletionTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray(): void
    {
        $suggest = new Completion('suggestName', 'fieldName');
        $suggest->setPrefix('foo');
        $suggest->setSize(10);
        $expected = [
            'prefix' => 'foo',
            'completion' => [
                'size' => 10,
                'field' => 'fieldName',
            ],
        ];
        $this->assertEquals($expected, $suggest->toArray());
    }

    /**
     * @group functional
     */
    public function testSuggestWorks(): void
    {
        $suggest = new Completion('suggestName', 'fieldName');
        $suggest->setPrefix('Never');

        $index = $this->_getIndexForTest();
        $resultSet = $index->search(Query::create($suggest));

        $this->assertTrue($resultSet->hasSuggests());

        $suggests = $resultSet->getSuggests();
        $options = $suggests['suggestName'][0]['options'];

        $this->assertCount(1, $options);
        $this->assertEquals('Nevermind', $options[0]['text']);
    }

    /**
     * @group functional
     */
    public function testFuzzySuggestWorks(): void
    {
        $suggest = new Completion('suggestName', 'fieldName');
        $suggest->setFuzzy(['fuzziness' => 2]);
        $suggest->setPrefix('Neavermint');

        $index = $this->_getIndexForTest();
        $resultSet = $index->search(Query::create($suggest));

        $this->assertTrue($resultSet->hasSuggests());

        $suggests = $resultSet->getSuggests();
        $options = $suggests['suggestName'][0]['options'];

        $this->assertCount(1, $options);
        $this->assertEquals('Nevermind', $options[0]['text']);
    }

    /**
     * @group functional
     */
    public function testCompletion(): void
    {
        $suggest = new Completion('suggestName1', 'fieldName');
        $suggest->setPrefix('Neavermint');

        $suggest2 = new Completion('suggestName2', 'fieldName2');
        $suggest2->setPrefix('Neverdint');

        $sug = new Suggest();
        $sug->addSuggestion($suggest);
        $sug->addSuggestion($suggest2);
        $index = $this->_getIndexForTest();
        $query = Query::create($sug);

        $expectedSuggestions = [
            'suggestName1' => [
                0 => [
                    'text' => 'Neavermint',
                    'offset' => 0,
                    'length' => 10,
                    'options' => [],
                ],
            ],
            'suggestName2' => [
                0 => [
                    'text' => 'Neverdint',
                    'offset' => 0,
                    'length' => 9,
                    'options' => [],
                ],
            ],
        ];

        $resultSet = $index->search($query);

        $this->assertTrue($resultSet->hasSuggests());
        $this->assertEquals($expectedSuggestions, $resultSet->getSuggests());
    }

    /**
     * @group unit
     */
    public function testSetFuzzy(): void
    {
        $suggest = new Completion('suggestName', 'fieldName');

        $fuzzy = [
            'unicode_aware' => true,
            'fuzziness' => 3,
        ];

        $suggest->setFuzzy($fuzzy);

        $this->assertEquals($fuzzy, $suggest->getParam('fuzzy'));

        $this->assertInstanceOf(Completion::class, $suggest->setFuzzy($fuzzy));
    }

    /**
     * @group functional
     */
    public function testRegexSuggestWorks(): void
    {
        $suggest = new Completion('suggestName', 'fieldName');
        $suggest->setRegex('n[ever|i]r');
        $suggest->setRegexOptions(['flags' => 'ANYSTRING', 'max_determinized_states' => 20000]);
        $index = $this->_getIndexForTest();
        $resultSet = $index->search(Query::create($suggest));
        $this->assertTrue($resultSet->hasSuggests());
        $suggests = $resultSet->getSuggests();
        $options = $suggests['suggestName'][0]['options'];
        $this->assertCount(3, $options);
    }

    /**
     * @return Index
     */
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();
        $index->setMapping(new Mapping([
            'fieldName' => [
                'type' => 'completion',
            ],
            'fieldName2' => [
                'type' => 'completion',
            ],
        ]));

        $index->addDocuments([
            new Document(1, [
                'fieldName' => [
                    'input' => ['Nevermind', 'Nirvana'],
                    'weight' => 5,
                ],
            ]),
            new Document(2, [
                'fieldName' => [
                    'input' => ['Bleach', 'Nirvana'],
                    'weight' => 2,
                ],
            ]),
            new Document(3, [
                'fieldName' => [
                    'input' => ['Incesticide', 'Nirvana'],
                    'weight' => 7,
                ],
            ]),
            new Document(4, [
                'fieldName2' => [
                    'input' => ['Bleach', 'Nirvana'],
                    'weight' => 3,
                ],
            ]),
            new Document(5, [
                'fieldName2' => [
                    'input' => ['Incesticide', 'Nirvana'],
                    'weight' => 3,
                ],
            ]),
        ]);

        $index->refresh();

        return $index;
    }
}
