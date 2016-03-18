<?php

namespace Elastica\Test;

use Elastica\Script as LegacyScript;
use Elastica\Test\Base as BaseTest;

class LegacyScriptTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testParent()
    {
        $this->assertInstanceOf('Elastica\Script\Script', new LegacyScript('script'));
    }
}
