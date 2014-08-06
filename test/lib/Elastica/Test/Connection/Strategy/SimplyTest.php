<?php

namespace Elastica\Test\Connection\Strategy;

use Elastica\Test\Base;

/**
 * Description of SimplyTest
 *
 * @author chabior
 */
class SimplyTest extends Base
{
    public function testConnection()
    {
        $client = new \Elastica\Client();
        $client->request('/_aliases');
    }
}
