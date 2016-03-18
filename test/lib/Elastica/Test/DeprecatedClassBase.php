<?php

namespace Elastica\Test;

/**
 * Base test for test deprecated classes. Supress deprecated error during run test case.
 *
 * @author Evgeniy Sokolov <ewgraf@gmail.com>
 */
class DeprecatedClassBase extends Base
{
    private $isDeprecatedVisible = false;

    protected function setUp()
    {
        parent::setUp();
        $this->hideDeprecated();
    }

    protected function tearDown()
    {
        $this->showDeprecated();
        parent::tearDown();
    }
}
