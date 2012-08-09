<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_PrefixTest extends Elastica_Test
{

    public function testToArray()
    {
        $query = new Elastica_Query_Prefix();
        $key = 'name';
        $value = 'ni';
        $boost = 2;
        $query->setPrefix($key, $value, $boost);

        $data = $query->toArray();

        $this->assertInternalType('array', $data['prefix']);
        $this->assertInternalType('array', $data['prefix'][$key]);
        $this->assertEquals($data['prefix'][$key]['value'], $value);
        $this->assertEquals($data['prefix'][$key]['boost'], $boost);
    }
}
