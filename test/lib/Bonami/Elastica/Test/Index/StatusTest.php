<?php
namespace Elastica\Test\Index;

use Elastica\Index\Status as IndexStatus;
use Elastica\Test\Base as BaseTest;

class StatusTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testGetAliases()
    {
        $indexName = 'test';
        $aliasName = 'test-alias';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create(array(), true);

        $status = new IndexStatus($index);

        $aliases = $status->getAliases();

        $this->assertTrue(empty($aliases));
        $this->assertInternalType('array', $aliases);

        $index->addAlias($aliasName);
        $status->refresh();

        $aliases = $status->getAliases();

        $this->assertTrue(in_array($aliasName, $aliases));
    }

    /**
     * @group functional
     */
    public function testHasAlias()
    {
        $indexName = 'test';
        $aliasName = 'test-alias';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create(array(), true);

        $status = new IndexStatus($index);

        $this->assertFalse($status->hasAlias($aliasName));

        $index->addAlias($aliasName);
        $status->refresh();

        $this->assertTrue($status->hasAlias($aliasName));
    }

    /**
     * @group functional
     */
    public function testGetSettings()
    {
        $indexName = 'test';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create(array(), true);
        $status = $index->getStatus();

        $settings = $status->getSettings();
        $this->assertInternalType('array', $settings);
        $this->assertTrue(isset($settings['index']['number_of_shards']));
    }
}
