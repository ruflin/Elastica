<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;
use Elastica\Query\CombinedFields;
use Elastica\ResultSet;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class CombinedFieldsQueryTest extends BaseTest
{
    private static $data = [
        ['id' => '1', 'title' => 'Rodolfo', 'body' => 'Moraes',   'abstract' => 'Lorem'],
        ['id' => '2', 'title' => 'Tristan', 'body' => 'Maindron', 'abstract' => 'Dolor'],
        ['id' => '3', 'title' => 'Monique', 'body' => 'Maindron', 'abstract' => 'Ipsum'],
        ['id' => '4', 'title' => 'John',    'body' => 'not Doe',  'abstract' => 'Consectetur'],
    ];

    #[Group('functional')]
    public function testMinimumShouldMatch(): void
    {
        // Note that the OR operator is the default.
        $combinedFields = new CombinedFields();
        $combinedFields->setQuery('Tristan Maindron');
        $combinedFields->setFields(['title', 'body', 'abstract']);
        $combinedFields->setMinimumShouldMatch('2<100%');
        $resultSet = $this->_getResults($combinedFields);

        $this->assertEquals(1, $resultSet->count());
    }

    #[Group('functional')]
    public function testAndOperator(): void
    {
        $combinedFields = new CombinedFields();
        $combinedFields->setQuery('Monique Maindron');
        $combinedFields->setFields(['title', 'body', 'abstract']);
        $combinedFields->setOperator(CombinedFields::OPERATOR_AND);
        $resultSet = $this->_getResults($combinedFields);

        $this->assertEquals(1, $resultSet->count());
    }

    #[Group('functional')]
    public function testZeroTerm(): void
    {
        $combinedFields = new CombinedFields();
        $combinedFields->setQuery('not'); // This is a stopword.
        $combinedFields->setFields(['title', 'body', 'abstract']);
        $combinedFields->setZeroTermsQuery(CombinedFields::ZERO_TERM_NONE);
        $resultSet = $this->_getResults($combinedFields);

        $this->assertEquals(1, $resultSet->count());

        $combinedFields->setZeroTermsQuery(CombinedFields::ZERO_TERM_ALL);
        $resultSet = $this->_getResults($combinedFields);

        $this->assertEquals(1, $resultSet->count());
    }

    #[Group('functional')]
    public function testBaseCombinedFields(): void
    {
        $combinedFields = new CombinedFields();
        $combinedFields->setQuery('Rodolfo Moraes');
        $combinedFields->setFields(['title', 'body', 'abstract']);
        $resultSet = $this->_getResults($combinedFields);

        $this->assertEquals(1, $resultSet->count());

        $combinedFields = new CombinedFields();
        $combinedFields->setQuery('Doe John');
        $combinedFields->setFields(['title', 'body', 'abstract']);
        $resultSet = $this->_getResults($combinedFields);

        $this->assertEquals(1, $resultSet->count());

        $combinedFields = new CombinedFields();
        $combinedFields->setQuery('John Doe Consectetur');
        $combinedFields->setFields(['title', 'body', 'abstract']);
        $resultSet = $this->_getResults($combinedFields);

        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * Executes the query with the current multimatch.
     */
    private function _getResults(CombinedFields $combinedFields): ResultSet
    {
        return $this->_generateIndex()->search(new Query($combinedFields));
    }

    /**
     * Builds an index for testing.
     */
    private function _generateIndex(): Index
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');

        $mapping = new Mapping([
            'title' => ['type' => 'text', 'analyzer' => 'noStops'],
            'body' => ['type' => 'text', 'analyzer' => 'noStops'],
            'abstract' => ['type' => 'text', 'analyzer' => 'noStops'],
        ]);

        $index->create(
            [
                'settings' => [
                    'analysis' => [
                        'analyzer' => [
                            'noStops' => [
                                'type' => 'standard',
                                'stopwords' => '_none_',
                            ],
                            'stops' => [
                                'type' => 'standard',
                                'stopwords' => ['not'],
                            ],
                        ],
                    ],
                ],
                'mappings' => $mapping->toArray(),
            ],
            [
                'recreate' => true,
            ]
        );

        foreach (self::$data as $key => $docData) {
            $index->addDocument(new Document((string) $key, $docData));
        }

        // Refresh index
        $index->refresh();

        return $index;
    }
}
