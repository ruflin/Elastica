<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\QueryString;
use Elastica\Test\Base as BaseTest;
use Elastica\Util;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class EscapeStringTest extends BaseTest
{
    #[Group('functional')]
    public function testSearch(): void
    {
        $index = $this->_createIndex();
        $index->getSettings()->setNumberOfReplicas(0);

        $doc = new Document(
            '1',
            [
                'email' => 'test@test.com', 'username' => 'test 7/6 123', 'test' => ['2', '3', '5'], ]
        );
        $index->addDocument($doc);

        // Refresh index
        $index->refresh();

        $queryString = new QueryString(Util::escapeTerm('test 7/6'));
        $resultSet = $index->search($queryString);

        $this->assertEquals(1, $resultSet->count());
    }
}
