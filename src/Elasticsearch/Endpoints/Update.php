<?php

namespace Elastica\Elasticsearch\Endpoints;

use Elastica\CustomOptions;
use Elasticsearch\Endpoints\Update as BaseUpdate;

class Update extends BaseUpdate
{
    public function getParamWhitelist(): array
    {
        // We need this for BC compatibility
        return array_merge(
            parent::getParamWhitelist(),
            [
                'version',
                CustomOptions::REQUEST_TAGS,
            ]
        );
    }
}
