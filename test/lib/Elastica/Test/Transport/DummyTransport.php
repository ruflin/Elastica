<?php
namespace Elastica\Test\Transport;

use Elastica\Request;
use Elastica\Transport\AbstractTransport;

class DummyTransport extends AbstractTransport
{
    public function exec(Request $request, array $params)
    {
        // empty
    }
}
