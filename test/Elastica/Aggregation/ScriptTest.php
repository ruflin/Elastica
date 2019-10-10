<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Sum;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use Elastica\Script\Script;

class ScriptTest extends BaseAggregationTest
{
    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document('1', ['price' => 5]),
            new Document('2', ['price' => 8]),
            new Document('3', ['price' => 1]),
            new Document('4', ['price' => 3]),
        ]);

        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testAggregationScript()
    {
        $agg = new Sum('sum');
        $script = new Script("return doc['price'].value", null, Script::LANG_PAINLESS);
        $agg->setScript($script);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('sum');

        $this->assertEquals(5 + 8 + 1 + 3, $results['value']);
    }

    /**
     * @group functional
     */
    public function testAggregationScriptAsString()
    {
        $agg = new Sum('sum');
        $agg->setScript(new Script("doc['price'].value", null, Script::LANG_PAINLESS));

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('sum');

        $this->assertEquals(5 + 8 + 1 + 3, $results['value']);
    }

    /**
     * @group unit
     */
    public function testSetScript()
    {
        $aggregation = 'sum';
        $string = "doc['price'].value";
        $params = [
            'param1' => 'one',
            'param2' => 1,
        ];
        $lang = Script::LANG_PAINLESS;

        $agg = new Sum($aggregation);
        $script = new Script($string, $params, $lang);
        $agg->setScript($script);

        $array = $agg->toArray();

        $expected = [
            $aggregation => [
                'script' => [
                    'source' => $string,
                    'params' => $params,
                    'lang' => $lang,
                ],
            ],
        ];
        $this->assertEquals($expected, $array);
    }
}
