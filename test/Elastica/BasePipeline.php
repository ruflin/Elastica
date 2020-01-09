<?php

namespace Elastica\Test;

use Elastica\Pipeline;
use Elastica\Test\Base as BaseTest;

class BasePipeline extends BaseTest
{
    protected function _createPipeline(?string $id = null, string $description = ''): Pipeline
    {
        $id = $id ?: static::buildUniqueId();

        $pipeline = new Pipeline($this->_getClient());
        $pipeline->setId($id);
        $pipeline->setDescription($description);

        return $pipeline;
    }

    protected function tearDown()
    {
        $this->_createPipeline()->deletePipeline('*');
        parent::tearDown();
    }
}
