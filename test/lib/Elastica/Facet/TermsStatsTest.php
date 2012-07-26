<?php

require_once dirname( __FILE__ ) . '/../../../bootstrap.php';

class Elastica_Facet_TermsStatsTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {

    }

    public function tearDown()
    {

    }

    public function testQuery()
    {

        $client = new Elastica_Client();
        $index  = $client->getIndex( 'test' );
        $index->create( array( ), true );
        $type = $index->getType( 'helloworld' );

        $doc = new Elastica_Document( 1, array( 'name' => 'tom', 'paid' => 7 ) );
        $type->addDocument( $doc );
        $doc   = new Elastica_Document( 2, array( 'name' => 'tom', 'paid' => 2 ) );
        $type->addDocument( $doc );
        $doc   = new Elastica_Document( 3, array( 'name' => 'tom', 'paid' => 5 ) );
        $type->addDocument( $doc );
        $doc   = new Elastica_Document( 4, array( 'name' => 'mike', 'paid' => 13 ) );
        $type->addDocument( $doc );
        $doc   = new Elastica_Document( 5, array( 'name' => 'mike', 'paid' => 1 ) );
        $type->addDocument( $doc );
        $doc   = new Elastica_Document( 6, array( 'name' => 'mike', 'paid' => 15 ) );
        $type->addDocument( $doc );

        $facet = new Elastica_Facet_TermsStats( 'test' );
        $facet->setKeyField( 'name' );
        $facet->setValueField( 'paid' );

        $query = new Elastica_Query();
        $query->addFacet( $facet );
        $query->setQuery( new Elastica_Query_MatchAll() );

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
