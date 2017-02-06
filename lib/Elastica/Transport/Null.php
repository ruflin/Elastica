<?php
namespace Elastica\Transport;

trigger_error('Elastica\Transport\Null is deprecated. Use NullTransport instead. From PHP7 null is reserved word and this class will be removed in further Elastica releases', E_USER_DEPRECATED);

/**
 * Elastica Null Transport object.
 *
 * This class is for backward compatibility reason for all php < 7 versions. For PHP 7 and above use NullTransport as Null is reserved.
 *
 * @author James Boehmer <james.boehmer@jamesboehmer.com>
 *
 * @deprecated Use NullTransport instead. From PHP7 null is reserved word and this class will be removed in further Elastica releases
 */
class Null extends NullTransport
{
}
