<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastica\Exception\NotImplementedException;
use Elastica\Query\BoolQuery;
use Elastica\Suggest;
use Elastica\Suggest\Term;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('unit')]
class SuggestTest extends BaseTest
{
    /**
     * Create self test.
     */
    public function testCreateSelf(): void
    {
        $suggest = new Suggest();

        $selfSuggest = Suggest::create($suggest);

        $this->assertSame($suggest, $selfSuggest);
    }

    /**
     * Create with suggest test.
     */
    public function testCreateWithSuggest(): void
    {
        $suggest1 = new Term('suggest1', '_all');

        $suggest = Suggest::create($suggest1);

        $this->assertTrue($suggest->hasParam('suggestion'));
    }

    public function testCreateWithNonSuggest(): void
    {
        $this->expectException(NotImplementedException::class);

        Suggest::create(new BoolQuery());
    }
}
