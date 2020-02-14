<?php

namespace Elastica\Test;

use Elastica\Pipeline;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class BasePipeline extends BaseTest
{
    protected function tearDown(): void
    {
        $this->_createPipeline()->deletePipeline('*');
        parent::tearDown();
    }

    protected function _createPipeline(?string $id = null, string $description = ''): Pipeline
    {
        $id = $id ?: static::buildUniqueId();

        $pipeline = new Pipeline($this->_getClient());
        $pipeline->setId($id);
        $pipeline->setDescription($description);

        return $pipeline;
    }
}
