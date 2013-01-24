<?php

namespace Elastica\Test\Facet;

use Elastica\Document;
use Elastica\Facet\TermsStats;
use Elastica\Query;
use Elastica\Query\MatchAll;
use Elastica\Test\Base as BaseTest;

class TermsStatsTest extends BaseTest
{
    public function testQuery()
    {
        $client = $this->_getClient();
        $index  = $client->getIndex( 'test' );
        $index->create( array( ), true );
        $type = $index->getType( 'helloworld' );

        $doc = new Document( 1, array( 'name' => 'tom', 'paid' => 7 ) );
        $type->addDocument( $doc );
        $doc   = new Document( 2, array( 'name' => 'tom', 'paid' => 2 ) );
        $type->addDocument( $doc );
        $doc   = new Document( 3, array( 'name' => 'tom', 'paid' => 5 ) );
        $type->addDocument( $doc );
        $doc   = new Document( 4, array( 'name' => 'mike', 'paid' => 13 ) );
        $type->addDocument( $doc );
        $doc   = new Document( 5, array( 'name' => 'mike', 'paid' => 1 ) );
        $type->addDocument( $doc );
        $doc   = new Document( 6, array( 'name' => 'mike', 'paid' => 15 ) );
        $type->addDocument( $doc );

        $facet = new TermsStats( 'test' );
        $facet->setKeyField( 'name' );
        $facet->setValueField( 'paid' );

        $query = new Query();
        $query->addFacet( $facet );
        $query->setQuery( new MatchAll() );

        $index->refresh();

        $response = $type->search( $query );
        $facets   = $response->getFacets();

        $this->assertEquals( 2, count( $facets[ 'test' ][ 'terms' ] ) );
        foreach ($facets[ 'test' ][ 'terms' ] as $facet) {
            if ($facet[ 'term' ] === 'tom') {
                $this->assertEquals( 14, $facet[ 'total' ] );
            }
            if ($facet[ 'term' ] === 'mike') {
                $this->assertEquals( 29, $facet[ 'total' ] );
            }
        }
    }
}
