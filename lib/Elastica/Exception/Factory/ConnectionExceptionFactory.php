<?php
/**
 * Created by PhpStorm.
 * User: thomas
 * Date: 2015/11/23
 * Time: 10:53 PM
 */

namespace Elastica\Exception\Factory;


use Elastica\Exception\Connection\GuzzleException;
use Elastica\Exception\ConnectionException;
use Elastica\Transport\AbstractTransport;
use GuzzleHttp\Exception\TransferException;

/**
 * TODO - Determine if this is actually going to work (and if it's even proper OOP design???)
 * The idea here is to pass in the Transport type,
 * and from that we should generate the appropriate
 * ConnectionException
 *
 * Class ConnectionExceptionFactory
 * @package Elastica\Exception\Factory
 */
class ConnectionExceptionFactory
{
    /**
     * @param AbstractTransport $transport
     * @return ConnectionException
     */
    public static function getConcreteConnectionException(AbstractTransport $transport)
    {
        return new GuzzleException(new TransferException());
    }
}
