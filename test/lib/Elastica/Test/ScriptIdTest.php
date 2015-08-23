<?php
namespace Elastica\Test;

use Elastica\Document;
use Elastica\Query;
use Elastica\Script;
use Elastica\ScriptFile;
use Elastica\ScriptId;
use Elastica\Test\Base as BaseTest;
use Elastica\Type;
use Elastica\Type\Mapping;

class ScriptIdTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testSearch()
    {
        $index = $this->_createIndex();

        $script = new Script("doc['location'].arcDistanceInKm(lat,lon)");
        $index->getClient()->addIndexedScript($script, 'groovy', 'indexedCalculateDistance');

        $type = $index->getType('test');

        $type->setMapping(new Mapping(null, array(
            'location' => array('type' => 'geo_point'),
        )));

        $type->addDocuments(array(
            new Document(1, array('location' => array('lat' => 48.8825968, 'lon' => 2.3706111))),
            new Document(2, array('location' => array('lat' => 48.9057932, 'lon' => 2.2739735))),
        ));

        $index->refresh();

        $scriptId = new ScriptId('indexedCalculateDistance', array('lat' => 48.858859, 'lon' => 2.3470599));

        $query = new Query();
        $query->addScriptField('distance', $scriptId);

        $resultSet = $type->search($query);
        $results = $resultSet->getResults();

        $this->assertEquals(2, $resultSet->count());
        $this->assertEquals(array(3.1494078652615), $results[0]->__get('distance'));
        $this->assertEquals(array(7.4639825876924561), $results[1]->__get('distance'));
    }

    /**
     * @group unit
     */
    public function testConstructor()
    {
        $value = 'indexedCalculateDistance';
        $scriptId = new ScriptId($value);

        $expected = array(
            'script_id' => $value,
        );
        $this->assertEquals($value, $scriptId->getScript());
        $this->assertEquals($expected, $scriptId->toArray());

        $params = array(
            'param1' => 'one',
            'param2' => 10,
        );

        $scriptId = new ScriptId($value, $params);

        $expected = array(
            'script_id' => $value,
            'params' => $params,
        );

        $this->assertEquals($value, $scriptId->getScript());
        $this->assertEquals($params, $scriptId->getParams());
        $this->assertEquals($expected, $scriptId->toArray());
    }

    /**
     * @group unit
     */
    public function testCreateString()
    {
        $string = 'indexedCalculateDistance';
        $scriptId = ScriptId::create($string);

        $this->assertInstanceOf('Elastica\ScriptId', $scriptId);

        $this->assertEquals($string, $scriptId->getScript());

        $expected = array(
            'script_id' => $string,
        );
        $this->assertEquals($expected, $scriptId->toArray());
    }

    /**
     * @group unit
     */
    public function testCreateScriptFile()
    {
        $data = new ScriptId('indexedCalculateDistance');

        $scriptId = ScriptId::create($data);

        $this->assertInstanceOf('Elastica\ScriptId', $scriptId);
        $this->assertSame($data, $scriptId);
    }

    /**
     * @group unit
     */
    public function testCreateArray()
    {
        $string = 'indexedCalculateDistance';
        $params = array(
            'param1' => 'one',
            'param2' => 1,
        );
        $array = array(
            'script_id' => $string,
            'params' => $params,
        );

        $scriptId = ScriptId::create($array);

        $this->assertInstanceOf('Elastica\ScriptId', $scriptId);

        $this->assertEquals($string, $scriptId->getScript());
        $this->assertEquals($params, $scriptId->getParams());

        $this->assertEquals($array, $scriptId->toArray());
    }

    /**
     * @group unit
     * @dataProvider dataProviderCreateInvalid
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testCreateInvalid($data)
    {
        ScriptFile::create($data);
    }

    /**
     * @return array
     */
    public function dataProviderCreateInvalid()
    {
        return array(
            array(
                new \stdClass(),
            ),
            array(
                array('params' => array('param1' => 'one')),
            ),
            array(
                array('params' => 'param'),
            ),
        );
    }

    /**
     * @group unit
     */
    public function testSetScript()
    {
        $scriptId = new ScriptId('foo');
        $this->assertEquals('foo', $scriptId->getScript());

        $scriptId->setScript('bar');
        $this->assertEquals('bar', $scriptId->getScript());

        $this->assertInstanceOf('Elastica\ScriptId', $scriptId->setScript('foo'));
    }
}
