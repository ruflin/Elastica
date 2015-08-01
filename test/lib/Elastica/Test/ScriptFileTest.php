<?php
namespace Elastica\Test;

use Elastica\Script;
use Elastica\ScriptFile;
use Elastica\Test\Base as BaseTest;

class ScriptFileTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testConstructor()
    {
        $value = "calculate-distance.groovy";
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
