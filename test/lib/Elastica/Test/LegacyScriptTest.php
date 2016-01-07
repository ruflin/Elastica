<?php
namespace Elastica\Test;

use Elastica\Script as LegacyScript;
use Elastica\Script\Script;
use Elastica\Test\Base as BaseTest;

class LegacyScriptTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testParent()
    {
        $this->assertInstanceOf(Script::class, new LegacyScript('script'));
    }
}