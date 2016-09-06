<?php

namespace Elastica\Test\Transport;

use Elastica\Transport\AbstractTransport;
use Elastica\Request;

class DummyTransport extends AbstractTransport
{
    public function exec(Request $request, array $params)
    {
        // empty
    }
}
