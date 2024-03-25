<?php

declare(strict_types=1);

namespace Elastica;

use Elastic\Elasticsearch\Response\Elasticsearch;

/**
 * @author PK <projekty@pawelkeska.eu>
 */
class ResponseConverter
{
    private function __construct()
    {
    }

    public static function toElastica(Elasticsearch $response): Response
    {
        return new Response($response->asString(), $response->getStatusCode());
    }
}
