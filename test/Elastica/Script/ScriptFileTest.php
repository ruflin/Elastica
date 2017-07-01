<?php
namespace Elastica\Test;

use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Query;
use Elastica\Script\ScriptFile;
use Elastica\Test\Base as BaseTest;
use Elastica\Type;
use Elastica\Type\Mapping;

class ScriptFileTest extends BaseTest
{
    const SCRIPT_FILE = 'calculate-distance';

    /**
     * @group functional
     */
    public function testSearch()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $type->setMapping(new Mapping(null, [
            'location' => ['type' => 'geo_point'],
        ]));

        $type->addDocuments([
            new Document(1, ['location' => ['lat' => 48.8825968, 'lon' => 2.3706111]]),
            new Document(2, ['location' => ['lat' => 48.9057932, 'lon' => 2.2739735]]),
        ]);

        $index->refresh();

        $scriptFile = new ScriptFile(self::SCRIPT_FILE, ['lat' => 48.858859, 'lon' => 2.3470599]);

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
        $this->assertEquals([3151.855706373115], $results[0]->__get('distance'));
        $this->assertEquals([7469.7862256855769], $results[1]->__get('distance'));
    }

    /**
     * @group unit
     */
    public function testConstructor()
    {
        $scriptFile = new ScriptFile(self::SCRIPT_FILE);

        $expected = [
            'script' => [
                'file' => self::SCRIPT_FILE,
            ],
        ];
        $this->assertEquals(self::SCRIPT_FILE, $scriptFile->getScriptFile());
        $this->assertEquals($expected, $scriptFile->toArray());

        $params = [
            'param1' => 'one',
            'param2' => 10,
        ];

        $scriptFile = new ScriptFile(self::SCRIPT_FILE, $params);

        $expected = [
            'script' => [
                'file' => self::SCRIPT_FILE,
                'params' => $params,
            ],
        ];

        $this->assertEquals(self::SCRIPT_FILE, $scriptFile->getScriptFile());
        $this->assertEquals($params, $scriptFile->getParams());
        $this->assertEquals($expected, $scriptFile->toArray());
    }

    /**
     * @group unit
     */
    public function testCreateString()
    {
        $scriptFile = ScriptFile::create(self::SCRIPT_FILE);

        $this->assertInstanceOf(ScriptFile::class, $scriptFile);

        $this->assertEquals(self::SCRIPT_FILE, $scriptFile->getScriptFile());

        $expected = [
            'script' => [
                'file' => self::SCRIPT_FILE,
            ],
        ];
        $this->assertEquals($expected, $scriptFile->toArray());
    }

    /**
     * @group unit
     */
    public function testCreateScriptFile()
    {
        $data = new ScriptFile(self::SCRIPT_FILE);

        $scriptFile = ScriptFile::create($data);

        $this->assertInstanceOf(ScriptFile::class, $scriptFile);
        $this->assertSame($data, $scriptFile);
    }

    /**
     * @group unit
     */
    public function testCreateArray()
    {
        $params = [
            'param1' => 'one',
            'param2' => 1,
        ];
        $array = [
            'script' => [
                'file' => self::SCRIPT_FILE,
                'params' => $params,
            ],
        ];

        $scriptFile = ScriptFile::create($array);

        $this->assertInstanceOf(ScriptFile::class, $scriptFile);
        $this->assertEquals($array, $scriptFile->toArray());

        $this->assertEquals(self::SCRIPT_FILE, $scriptFile->getScriptFile());
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
        return [
            [
                new \stdClass(),
            ],
            [
                ['params' => ['param1' => 'one']],
            ],
            [
                ['script' => 'calculate-distance.painless', 'params' => 'param'],
            ],
        ];
    }

    /**
     * @group unit
     */
    public function testSetScriptFile()
    {
        $scriptFile = new ScriptFile(self::SCRIPT_FILE);

        $this->assertSame($scriptFile, $scriptFile->setScriptFile('bar'));
        $this->assertEquals('bar', $scriptFile->getScriptFile());
    }
}
