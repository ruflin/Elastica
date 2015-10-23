<?php
namespace Elastica\Test;

use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Query;
use Elastica\ScriptFile;
use Elastica\Test\Base as BaseTest;
use Elastica\Type;
use Elastica\Type\Mapping;

class ScriptFileTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testSearch()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $type->setMapping(new Mapping(null, array(
            'location' => array('type' => 'geo_point'),
        )));

        $type->addDocuments(array(
            new Document(1, array('location' => array('lat' => 48.8825968, 'lon' => 2.3706111))),
            new Document(2, array('location' => array('lat' => 48.9057932, 'lon' => 2.2739735))),
        ));

        $index->refresh();

        $scriptFile = new ScriptFile('calculate-distance', array('lat' => 48.858859, 'lon' => 2.3470599));

        $query = new Query();
        $query->addScriptField('distance', $scriptFile);

        try {
            $resultSet = $type->search($query);
        } catch (ResponseException $e) {
            if (strpos($e->getMessage(), 'Unable to find on disk script') !== false) {
                $this->markTestIncomplete('calculate-distance script not installed?');
            }

            throw $e;
        }

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
        $value = 'calculate-distance.groovy';
        $scriptFile = new ScriptFile($value);

        $expected = array(
            'script_file' => $value,
        );
        $this->assertEquals($value, $scriptFile->getScriptFile());
        $this->assertEquals($expected, $scriptFile->toArray());

        $params = array(
            'param1' => 'one',
            'param2' => 10,
        );

        $scriptFile = new ScriptFile($value, $params);

        $expected = array(
            'script_file' => $value,
            'params' => $params,
        );

        $this->assertEquals($value, $scriptFile->getScriptFile());
        $this->assertEquals($params, $scriptFile->getParams());
        $this->assertEquals($expected, $scriptFile->toArray());
    }

    /**
     * @group unit
     */
    public function testCreateString()
    {
        $string = 'calculate-distance.groovy';
        $scriptFile = ScriptFile::create($string);

        $this->assertInstanceOf('Elastica\ScriptFile', $scriptFile);

        $this->assertEquals($string, $scriptFile->getScriptFile());

        $expected = array(
            'script_file' => $string,
        );
        $this->assertEquals($expected, $scriptFile->toArray());
    }

    /**
     * @group unit
     */
    public function testCreateScriptFile()
    {
        $data = new ScriptFile('calculate-distance.groovy');

        $scriptFile = ScriptFile::create($data);

        $this->assertInstanceOf('Elastica\ScriptFile', $scriptFile);
        $this->assertSame($data, $scriptFile);
    }

    /**
     * @group unit
     */
    public function testCreateArray()
    {
        $string = 'calculate-distance.groovy';
        $params = array(
            'param1' => 'one',
            'param2' => 1,
        );
        $array = array(
            'script_file' => $string,
            'params' => $params,
        );

        $scriptFile = ScriptFile::create($array);

        $this->assertInstanceOf('Elastica\ScriptFile', $scriptFile);

        $this->assertEquals($string, $scriptFile->getScriptFile());
        $this->assertEquals($params, $scriptFile->getParams());

        $this->assertEquals($array, $scriptFile->toArray());
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
                array('script' => 'calculate-distance.groovy', 'params' => 'param'),
            ),
        );
    }

    /**
     * @group unit
     */
    public function testSetScriptFile()
    {
        $scriptFile = new ScriptFile('foo');
        $this->assertEquals('foo', $scriptFile->getScriptFile());

        $scriptFile->setScriptFile('bar');
        $this->assertEquals('bar', $scriptFile->getScriptFile());

        $this->assertInstanceOf('Elastica\ScriptFile', $scriptFile->setScriptFile('foo'));
    }
}
