<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\SpanFirst;
use Elastica\Query\SpanTerm;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class SpanFirstTest extends BaseTest
{
    #[Group('unit')]
    public function testToArray(): void
    {
        $query = new SpanFirst();
        $query->setMatch(new SpanTerm(['user' => 'kimchy']));
        $query->setEnd(3);

        $data = $query->toArray();

        $this->assertEquals([
            'span_first' => [
                'match' => [
                    'span_term' => ['user' => 'kimchy'],
                ],
                'end' => 3,
            ],
        ], $data);
    }

    #[Group('functional')]
    public function testSpanNearTerm(): void
    {
        $field = 'lorem';
        $value = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse odio lacus, aliquam nec nulla quis, aliquam eleifend eros.';

        $index = $this->_createIndex();

        $docHitData = [$field => $value];
        $doc = new Document('1', $docHitData);
        $index->addDocument($doc);
        $index->refresh();

        $spanTerm = new SpanTerm([$field => ['value' => 'consectetur']]);

        // consectetur, end 4 won't match
        $spanNearQuery = new SpanFirst($spanTerm, 4);
        $resultSet = $index->search($spanNearQuery);
        $this->assertEquals(0, $resultSet->count());

        $spanTerm = new SpanTerm([$field => ['value' => 'lorem']]);

        // lorem, end 3 matches
        $spanNearQuery = new SpanFirst($spanTerm, 3);
        $resultSet = $index->search($spanNearQuery);
        $this->assertEquals(1, $resultSet->count());
    }
}
