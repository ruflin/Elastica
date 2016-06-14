<?php
namespace Elastica\Transport;

/**
 * Elastica Null Transport object.
 *
 * This class is for backward compatibility reason for all php < 7 versions. For PHP 7 and above use NullTransport as Null is reserved.
 *
 * @author James Boehmer <james.boehmer@jamesboehmer.com>
 */
class Null extends NullTransport
{
}
