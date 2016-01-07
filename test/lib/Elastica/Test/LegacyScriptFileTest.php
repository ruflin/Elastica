<?php
namespace lib\Elastica\Test;

use Elastica\ScriptFile as LegacyScriptFile;
use Elastica\Script\ScriptFile;
use Elastica\Test\Base as BaseTest;

class LegacyScriptFileTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testParent()
    {
        $this->assertInstanceOf(ScriptFile::class, new LegacyScriptFile('script_file'));
    }
}