<?php
namespace Elastica\Test\Transformer;

use Elastica\Query;
use Elastica\Response;
use Elastica\ResultSet;
use Elastica\ResultSet\ChainProcessor;
use Elastica\Test\Base as BaseTest;

/**
 * @group unit
 */
class ChainProcessorTest extends BaseTest
{
    public function testProcessor()
    {
        $processor = new ChainProcessor([
            $processor1 = $this->getMock('Elastica\\ResultSet\\ProcessorInterface'),
            $processor2 = $this->getMock('Elastica\\ResultSet\\ProcessorInterface'),
        ]);
        $resultSet = new ResultSet(new Response(''), new Query(), []);

        $processor1->expects($this->once())
            ->method('process')
            ->with($resultSet);
        $processor2->expects($this->once())
            ->method('process')
            ->with($resultSet);

        $processor->process($resultSet);
    }
}
