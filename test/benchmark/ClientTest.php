<?php

use Elastica\Client;
use Elastica\Document;

class ClientTest extends \PHPUnit_Framework_TestCase
{

    public function testServersArray()
    {
        $client = new Client();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('test');

        $start = microtime(true);

        for ($i = 1; $i <= 10000; $i++) {
            $doc = new Document($i, array('test' => 1));
            $type->addDocument($doc);
        }

        // Refresh index
        $index->refresh();

        $end = microtime(true);

        //echo $end - $start;
    }
}
