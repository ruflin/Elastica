<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_FieldTest extends PHPUnit_Framework_TestCase
{
    public function testToArray()
    {
        $query = new Elastica_Query_Field('user', 'jack');
        $expected = array('field' => array('user' => array('query' => 'jack')));

        $this->assertSame($expected, $query->toArray());
    }
}
