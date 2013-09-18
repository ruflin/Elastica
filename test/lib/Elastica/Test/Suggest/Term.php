<?php

namespace Elastica\Test\Suggest;

use Elastica\Test\Base as BaseTest;
use Elastica\Suggest\Term;
use Elastica\Query;

class TermTest extends BaseTest
{
    public function testToArrayOneTerm()
    {
        $suggest = new Term();
        $suggest->addTerm('suggest1', array('text' => 'Foor', 'term' => array('field' => '_all', 'size' => 4)));

        $query = new Query();
        $query->addSuggest($suggest);

        $expectedArray = array(
                'suggest1' => array(
                    'text' => 'Foor',
                    'term' => array(
                        'field' => '_all',
                        'size' => 4)
                    )
                );
        $this->assertEquals($expectedArray, $query->toArray());
    }

    public function testToArrayMultipleTerms()
    {
        $suggest = new Term();
        $suggest->addTerm('suggest1', array('text' => 'Foor', 'term' => array('field' => '_all', 'size' => 4)));
        $suggest->addTerm('suggest2', array('text' => 'Fool', 'term' => array('field' => '_all', 'size' => 4)));

        $query = new Query();
        $query->addSuggest($suggest);

        $expectedArray = array(
            'suggest1' => array(
                    'text' => 'Foor',
                    'term' => array(
                        'field' => '_all',
                        'size' => 4)
                    ),
            'suggest2' => array(
                    'text' => 'Fool',
                    'term' => array(
                        'field' => '_all',
                        'size' => 4)
                    )
            );

        $this->assertEquals($expectedArray, $query->toArray());
    }
}