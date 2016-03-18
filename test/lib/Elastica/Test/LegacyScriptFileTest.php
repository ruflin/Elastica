<?php

namespace lib\Elastica\Test;

use Elastica\ScriptFile as LegacyScriptFile;
use Elastica\Test\Base as BaseTest;

class LegacyScriptFileTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testParent()
    {
        $this->assertInstanceOf('Elastica\Script\ScriptFile', new LegacyScriptFile('script_file'));
    }
}
