<?php

namespace lib\Elastica\Test;

use Elastica\ScriptFields as LegacyScriptFields;
use Elastica\Test\Base as BaseTest;

class LegacyScriptFieldsTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testParent()
    {
        $this->assertInstanceOf('Elastica\Script\ScriptFields', new LegacyScriptFields(array()));
    }
}
