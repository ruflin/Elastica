<?php

namespace Elastica\Test;

/**
 * Base test for test deprecated classes. Suppress deprecated error during run test case.
 *
 * @author Evgeniy Sokolov <ewgraf@gmail.com>
 */
class DeprecatedClassBase extends Base
{
    protected function set_up()
    {
        parent::set_up();
        $this->hideDeprecated();
    }

    protected function tear_down()
    {
        $this->showDeprecated();
        parent::tear_down();
    }
}
