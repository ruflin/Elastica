<?php
namespace Elastica;

/**
 * Elastica tools.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Oleg Zinchenko <olegz@default-value.com>
 * @author Roberto Nygaard <roberto@nygaard.es>
 */
class Util
{
    /** @var array */
    protected static $dateMathSymbols = ['<', '>', '/', '{', '}', '|', '+', ':', ','];

    /** @var array */
    protected static $escapedDateMathSymbols = ['%3C', '%3E', '%2F', '%7B', '%7D', '%7C', '%2B', '%3A', '%2C'];

    /**
     * Checks if date math is already escaped within request URI.
     *
     * @param string $requestUri
     *
     * @return bool
     */
    public static function isDateMathEscaped($requestUri)
    {
        // In practice, the only symbol that really needs to be escaped in URI is '/' => '%2F'
        return false !== strpos(strtoupper($requestUri), '%2F');
    }

    /**
     * Escapes date math symbols within request URI.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/5.x/date-math-index-names.html
     *
     * @param string $requestUri
     *
     * @return string
     */
    public static function escapeDateMath($requestUri)
    {
        if (empty($requestUri)) {
            return $requestUri;
        }

        // Check if date math if used at all. Find last '>'. E.g. /<log-{now/d}>,log-2011.12.01/log/_refresh
        $pos1 = strrpos($requestUri, '>');
        if (false === $pos1) {
            return $requestUri;
        }

        // Find the position up to which we should escape.
        // Should be next slash '/' after last '>' E.g. /<log-{now/d}>,log-2011.12.01/log/_refresh
        $pos2 = strpos($requestUri, '/', $pos1);
        $pos2 = false !== $pos2 ? $pos2 : strlen($requestUri);

        // Cut out the bit we need to escape: /<log-{now/d}>,log-2011.12.01
        $uriSegment = substr($requestUri, 0, $pos2);

        // Escape using character map
        $escapedUriSegment = str_replace(static::$dateMathSymbols, static::$escapedDateMathSymbols, $uriSegment);

        // '\\{' and '\\}' should not be escaped
        if (false !== strpos($uriSegment, '\\\\')) {
            $escapedUriSegment = str_replace(['\\\\%7B', '\\\\%7D'], ['\\\\{', '\\\\}'], $escapedUriSegment);
        }

        // Replace part of the string. E.g. /%3Clog-%7Bnow%2Fd%7D%3E%2Clog-2011.12.01/log/_refresh
        return substr_replace($requestUri, $escapedUriSegment, 0, $pos2);
    }

    /**
     * Replace the following reserved words: AND OR NOT
     * and
     * escapes the following terms: + - && || ! ( ) { } [ ] ^ " ~ * ? : \.
     *
     * @link http://lucene.apache.org/java/2_4_0/queryparsersyntax.html#Boolean%20operators
     * @link http://lucene.apache.org/java/2_4_0/queryparsersyntax.html#Escaping%20Special%20Characters
     *
     * @param string $term Query term to replace and escape
     *
     * @return string Replaced and escaped query term
     */
    public static function replaceBooleanWordsAndEscapeTerm($term)
    {
        $result = $term;
        $result = self::replaceBooleanWords($result);
        $result = self::escapeTerm($result);

        return $result;
    }

    /**
     * Escapes the following terms (because part of the query language)
     * + - && || ! ( ) { } [ ] ^ " ~ * ? : \ < >.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html#_reserved_characters
     *
     * @param string $term Query term to escape
     *
     * @return string Escaped query term
     */
    public static function escapeTerm($term)
    {
        $result = $term;
        // \ escaping has to be first, otherwise escaped later once again
        $chars = ['\\', '+', '-', '&&', '||', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?', ':', '/', '<', '>'];

        foreach ($chars as $char) {
            $result = str_replace($char, '\\'.$char, $result);
        }

        return $result;
    }

    /**
     * Replace the following reserved words (because part of the query language)
     * AND OR NOT.
     *
     * @link http://lucene.apache.org/java/2_4_0/queryparsersyntax.html#Boolean%20operators
     *
     * @param string $term Query term to replace
     *
     * @return string Replaced query term
     */
    public static function replaceBooleanWords($term)
    {
        $replacementMap = [' AND ' => ' && ', ' OR ' => ' || ', ' NOT ' => ' !'];
        $result = strtr($term, $replacementMap);

        return $result;
    }

    /**
     * Converts a snake_case string to CamelCase.
     *
     * For example: hello_world to HelloWorld
     *
     * @param string $string snake_case string
     *
     * @return string CamelCase string
     */
    public static function toCamelCase($string)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

    /**
     * Converts a CamelCase string to snake_case.
     *
     * For Example HelloWorld to hello_world
     *
     * @param string $string CamelCase String to Convert
     *
     * @return string SnakeCase string
     */
    public static function toSnakeCase($string)
    {
        $string = preg_replace('/([A-Z])/', '_$1', $string);

        return strtolower(substr($string, 1));
    }

    /**
     * Converts given time to format: 1995-12-31T23:59:59Z.
     *
     * This is the lucene date format
     *
     * @param int|string $date Date input (could be string etc.) -> must be supported by strtotime
     *
     * @return string Converted date string
     */
    public static function convertDate($date)
    {
        if (is_int($date)) {
            $timestamp = $date;
        } else {
            $timestamp = strtotime($date);
        }
        $string = date('Y-m-d\TH:i:s\Z', $timestamp);

        return $string;
    }

    /**
     * Convert a \DateTime object to format: 1995-12-31T23:59:59Z+02:00.
     *
     * Converts it to the lucene format, including the appropriate TimeZone
     *
     * @param \DateTime $dateTime
     * @param bool      $includeTimezone
     *
     * @return string
     */
    public static function convertDateTimeObject(\DateTime $dateTime, $includeTimezone = true)
    {
        $formatString = 'Y-m-d\TH:i:s'.($includeTimezone === true ? 'P' : '\Z');
        $string = $dateTime->format($formatString);

        return $string;
    }

    /**
     * Tries to guess the name of the param, based on its class
     * Example: \Elastica\Query\MatchAll => match_all.
     *
     * @param string|object Object or class name
     *
     * @return string parameter name
     */
    public static function getParamName($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $parts = explode('\\', $class);
        $last = array_pop($parts);
        $last = preg_replace('/Query$/', '', $last); // for BoolQuery

        return self::toSnakeCase($last);
    }

    /**
     * Converts Request to Curl console command.
     *
     * @param Request $request
     *
     * @return string
     */
    public static function convertRequestToCurlCommand(Request $request)
    {
        $message = 'curl -X'.strtoupper($request->getMethod()).' ';
        $message .= '\'http://'.$request->getConnection()->getHost().':'.$request->getConnection()->getPort().'/';
        $message .= $request->getPath();

        $query = $request->getQuery();
        if (!empty($query)) {
            $message .= '?'.http_build_query($query);
        }

        $message .= '\'';

        $data = $request->getData();
        if (!empty($data)) {
            $message .= ' -d \''.JSON::stringify($data).'\'';
        }

        return $message;
    }
}
