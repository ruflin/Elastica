<?php

namespace Elastica\Query;

\trigger_error('Elastica\Query\Match is deprecated. Use Elastica\Query\MatchQuery instead. From PHP 8 match is reserved word and this class will be removed in further Elastica releases', \E_USER_DEPRECATED);

/**
 * Match query.
 *
 * This class is for backward compatibility reason. For PHP 8 and above use MatchQuery as Match is reserved.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @deprecated Use MatchQuery instead. From PHP 8 match is reserved word and this class will be removed in further Elastica releases
 */
class Match extends MatchQuery
{
}
