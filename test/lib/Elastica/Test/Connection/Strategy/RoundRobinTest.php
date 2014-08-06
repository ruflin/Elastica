<?php

namespace Elastica\Test\Connection\Strategy;

use Elastica\Test\Base;

/**
 * Description of RoundRobinTest
 *
 * @author chabior
 */
class RoundRobinTest extends Base
{
   public function testConnection()
   {
       $config = array('connectionStrategy' => 'RoundRobin');
       $client = new \Elastica\Client($config);
       $client->request('/_aliases');
   }
}
