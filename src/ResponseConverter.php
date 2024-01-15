<?php

namespace Elastica;

use Elastic\Elasticsearch\Response\Elasticsearch;

/**
 * @author PK <projekty@pawelkeska.eu>
 */
class ResponseConverter
{
    public static function toElastica(Elasticsearch $elasticsearchResponse): Response
    {
        return new Response($elasticsearchResponse->asString(), $elasticsearchResponse->getStatusCode());
    }
}
