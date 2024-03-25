<?php

declare(strict_types=1);

namespace Elastica;

/**
 * Elastica Request object.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Request extends Param
{
    public const HEAD = 'HEAD';
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const GET = 'GET';
    public const DELETE = 'DELETE';
    public const DEFAULT_CONTENT_TYPE = 'application/json';
    public const NDJSON_CONTENT_TYPE = 'application/x-ndjson';
}
