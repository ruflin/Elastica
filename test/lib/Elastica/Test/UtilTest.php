<?php

namespace Elastica\Test;

use Elastica\Connection;
use Elastica\Request;
use Elastica\Test\Base as BaseTest;
use Elastica\Util;

class UtilTest extends BaseTest
{
    /**
     * @dataProvider getEscapeTermPairs
     */
    public function testEscapeTerm($unescaped, $escaped)
    {
        $this->assertEquals($escaped, Util::escapeTerm($unescaped));
    }

    public function getEscapeTermPairs()
    {
        return array(
            array('', ''),
            array('pragmatic banana', 'pragmatic banana'),
            array('oh yeah!', 'oh yeah\\!'),
            // Seperate test below because phpunit seems to have some problems
            //array('\\+-&&||!(){}[]^"~*?:', '\\\\\\+\\-\\&&\\||\\!\\(\\)\\{\\}\\[\\]\\^\\"\\~\\*\\?\\:'),
            array('some signs, can stay.', 'some signs, can stay.'),
        );
    }

    /**
     * @dataProvider getReplaceBooleanWordsPairs
     */
    public function testReplaceBooleanWords($before, $after)
    {
        $this->assertEquals($after, Util::replaceBooleanWords($before));
    }

    public function getReplaceBooleanWordsPairs()
    {
        return array(
            array('to be OR not to be', 'to be || not to be'),
            array('ORIGINAL GIFTS', 'ORIGINAL GIFTS'),
            array('Black AND White', 'Black && White'),
            array('TIMBERLAND Men`s', 'TIMBERLAND Men`s'),
            array('hello NOT kitty', 'hello !kitty'),
            array('SEND NOTIFICATION', 'SEND NOTIFICATION'),
        );
    }

    public function testEscapeTermSpecialCharacters()
    {
        $before = '\\+-&&||!(){}[]^"~*?:/';
        $after = '\\\\\\+\\-\\&&\\||\\!\\(\\)\\{\\}\\[\\]\\^\\"\\~\\*\\?\\:\\/';

        $this->assertEquals(Util::escapeTerm($before), $after);
    }

    public function testToCamelCase()
    {
        $string = 'hello_world';
        $this->assertEquals('HelloWorld', Util::toCamelCase($string));

        $string = 'how_are_you_today';
        $this->assertEquals('HowAreYouToday', Util::toCamelCase($string));
    }

    public function testToSnakeCase()
    {
        $string = 'HelloWorld';
        $this->assertEquals('hello_world', Util::toSnakeCase($string));

        $string = 'HowAreYouToday';
        $this->assertEquals('how_are_you_today', Util::toSnakeCase($string));
    }

    public function testConvertRequestToCurlCommand()
    {
        $path = 'test';
        $method = Request::POST;
        $query = array('no' => 'params');
        $data = array('key' => 'value');

        $connection = new Connection();
        $connection->setHost('localhost');
        $connection->setPort('9200');

        $request = new Request($path, $method, $data, $query, $connection);

        $curlCommand = Util::convertRequestToCurlCommand($request);

        $expected = 'curl -XPOST \'http://localhost:9200/test?no=params\' -d \'{"key":"value"}\'';
        $this->assertEquals($expected, $curlCommand);
    }

    public function testConvertDateTimeObjectWithTimezone()
    {
        $dateTimeObject = new \DateTime();
        $timestamp = $dateTimeObject->getTimestamp();

        $convertedString = Util::convertDateTimeObject($dateTimeObject);

        $date = date('Y-m-d\TH:i:sP', $timestamp);

        $this->assertEquals($convertedString, $date);
    }

    public function testConvertDateTimeObjectWithoutTimezone()
    {
        $dateTimeObject = new \DateTime();
        $timestamp = $dateTimeObject->getTimestamp();

        $convertedString = Util::convertDateTimeObject($dateTimeObject, false);

        $date = date('Y-m-d\TH:i:s\Z', $timestamp);

        $this->assertEquals($convertedString, $date);
    }
}
