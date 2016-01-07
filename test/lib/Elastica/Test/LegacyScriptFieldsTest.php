<?php
namespace lib\Elastica\Test;

use Elastica\ScriptFields as LegacyScriptFields;
use Elastica\Script\ScriptFields;
use Elastica\Test\Base as BaseTest;

class LegacyScriptFieldsTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testParent()
    {
        $this->assertInstanceOf(ScriptFields::class, new LegacyScriptFields([]));
    }
}