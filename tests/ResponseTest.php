<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastica\Response;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('unit')]
class ResponseTest extends BaseTest
{
    public function testIsOkBulkWithErrorsField(): void
    {
        $response = new Response(\json_encode([
            'took' => 213,
            'errors' => false,
            'items' => [
                ['index' => ['_index' => 'rohlik', '_id' => '707891', '_version' => 4, 'status' => 200]],
                ['index' => ['_index' => 'rohlik', '_id' => '707893', '_version' => 4, 'status' => 200]],
            ],
        ]));

        $this->assertTrue($response->isOk());
    }

    public function testIsNotOkBulkWithErrorsField(): void
    {
        $response = new Response(\json_encode([
            'took' => 213,
            'errors' => true,
            'items' => [
                ['index' => ['_index' => 'rohlik', '_id' => '707891', '_version' => 4, 'status' => 200]],
                ['index' => ['_index' => 'rohlik', '_id' => '707893', '_version' => 4, 'status' => 200]],
            ],
        ]));

        $this->assertFalse($response->isOk());
    }

    public function testIsOkBulkItemsWithOkField(): void
    {
        $response = new Response(\json_encode([
            'took' => 213,
            'items' => [
                ['index' => ['_index' => 'rohlik', '_id' => '707891', '_version' => 4, 'ok' => true]],
                ['index' => ['_index' => 'rohlik', '_id' => '707893', '_version' => 4, 'ok' => true]],
            ],
        ]));

        $this->assertTrue($response->isOk());
    }

    public function testStringErrorMessage(): void
    {
        $response = new Response(\json_encode([
            'error' => 'a',
        ]));

        $this->assertEquals('a', $response->getErrorMessage());
    }

    public function testArrayErrorMessage(): void
    {
        $response = new Response(\json_encode([
            'error' => ['a', 'b'],
        ]));

        $this->assertEquals(['a', 'b'], $response->getFullError());
    }

    public function testIsNotOkBulkItemsWithOkField(): void
    {
        $response = new Response(\json_encode([
            'took' => 213,
            'items' => [
                ['index' => ['_index' => 'rohlik', '_id' => '707891', '_version' => 4, 'ok' => true]],
                ['index' => ['_index' => 'rohlik', '_id' => '707893', '_version' => 4, 'ok' => false]],
            ],
        ]));

        $this->assertFalse($response->isOk());
    }

    public function testIsOkBulkItemsWithStatusField(): void
    {
        $response = new Response(\json_encode([
            'took' => 213,
            'items' => [
                ['index' => ['_index' => 'rohlik', '_id' => '707891', '_version' => 4, 'status' => 200]],
                ['index' => ['_index' => 'rohlik', '_id' => '707893', '_version' => 4, 'status' => 200]],
            ],
        ]));

        $this->assertTrue($response->isOk());
    }

    public function testIsNotOkBulkItemsWithStatusField(): void
    {
        $response = new Response(\json_encode([
            'took' => 213,
            'items' => [
                ['index' => ['_index' => 'rohlik', '_id' => '707891', '_version' => 4, 'status' => 200]],
                ['index' => ['_index' => 'rohlik', '_id' => '707893', '_version' => 4, 'status' => 301]],
            ],
        ]));

        $this->assertFalse($response->isOk());
    }

    public function testDecodeResponseWithBigIntSetToTrue(): void
    {
        $response = new Response(\json_encode([
            'took' => 213,
            'items' => [
                ['index' => ['_index' => 'rohlik', '_id' => '707891', '_version' => 4, 'status' => 200]],
                ['index' => ['_index' => 'rohlik', '_id' => '707893', '_version' => 4, 'status' => 200]],
            ],
        ]));
        $response->setJsonBigintConversion(true);

        $this->assertIsArray($response->getData());
    }
}
