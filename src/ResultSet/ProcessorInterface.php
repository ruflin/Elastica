<?php

namespace Elastica\ResultSet;

use Elastica\ResultSet;

interface ProcessorInterface
{
    /**
     * Iterates over a ResultSet allowing a processor to iterate over any
     * Results as required.
     *
     * @return void
     */
    public function process(ResultSet $resultSet);
}
