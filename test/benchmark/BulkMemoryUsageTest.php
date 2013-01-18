<?php

use Elastica\Client;
use Elastica\Document;

class BulkMemoryUsageTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Some memory usage stats
     *
     * Really simple and quite stupid ...
     */
    public function testServersArray()
    {
        $client = new Client();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('test');

        $data = array(
            'text1' => 'Very long text for a string',
            'text2' => 'But this is not very long',
            'text3' => 'random or not random?',
        );

        $startMemory = memory_get_usage();

        for ($n = 1; $n < 10; $n++) {
            $docs = array();

            for ($i = 1; $i <= 3000; $i++) {
                $docs[] = new Document(uniqid(), $data);
            }

            $type->addDocuments($docs);
            $docs = array();
        }

        unset($docs);

        $endMemory = memory_get_usage();

        $this->assertLessThan(1.2, $endMemory/$startMemory);
    }
}
