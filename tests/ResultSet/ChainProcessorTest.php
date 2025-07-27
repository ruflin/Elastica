<?php

declare(strict_types=1);

namespace Elastica\Test\ResultSet;

use Elastica\Query;
use Elastica\Response;
use Elastica\ResultSet;
use Elastica\ResultSet\ChainProcessor;
use Elastica\ResultSet\ProcessorInterface;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('unit')]
class ChainProcessorTest extends BaseTest
{
    public function testProcessor(): void
    {
        $processor = new ChainProcessor([
            $processor1 = $this->createMock(ProcessorInterface::class),
            $processor2 = $this->createMock(ProcessorInterface::class),
        ]);
        $resultSet = new ResultSet(new Response(''), new Query(), []);

        $processor1->expects($this->once())
            ->method('process')
            ->with($resultSet)
        ;
        $processor2->expects($this->once())
            ->method('process')
            ->with($resultSet)
        ;

        $processor->process($resultSet);
    }
}
