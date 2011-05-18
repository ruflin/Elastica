<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

class Elastica_UtilTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getEscapeTermPairs
     */
    public function testEscapeTerm($unescaped, $escaped)
    {
        $this->assertEquals($escaped, Elastica_Util::escapeTerm($unescaped));
    }

    public function getEscapeTermPairs()
    {
        return array(
            array('', ''),
            array('pragmatic banana', 'pragmatic banana'),
            array('oh yeah!', 'oh yeah\\!'),
            array('\\+-&&||!(){}[]^"~*?:', '\\\\\\+\\-\\&&\\||\\!\\(\\)\\{\\}\\[\\]\\^\\"\\~\\*\\?\\:'),
            array('some signs, can stay.', 'some signs, can stay.')
        );
    }
}
