<?php

namespace Elastica;

use Elastica\JSON;

/**
 * Elastica tools
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Oleg Zinchenko <olegz@default-value.com>
 */
class Util
{
    /**
     * Replace the following reserved words: AND OR NOT
     * and
     * escapes the following terms: + - && || ! ( ) { } [ ] ^ " ~ * ? : \
     *
     * @param  string $term Query term to replace and escape
     * @return string Replaced and escaped query term
     * @link http://lucene.apache.org/java/2_4_0/queryparsersyntax.html#Boolean%20operators
     * @link http://lucene.apache.org/java/2_4_0/queryparsersyntax.html#Escaping%20Special%20Characters
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
     * + - && || ! ( ) { } [ ] ^ " ~ * ? : \
     *
     * @param  string $term Query term to escape
     * @return string Escaped query term
     * @link http://lucene.apache.org/java/2_4_0/queryparsersyntax.html#Escaping%20Special%20Characters
     */
    public static function escapeTerm($term)
    {
        $result = $term;
        // \ escaping has to be first, otherwise escaped later once again
        $chars = array('\\', '+', '-', '&&', '||', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?', ':');

        foreach ($chars as $char) {
            $result = str_replace($char, '\\' . $char, $result);
        }

        // since elasticsearch uses lucene 4.0 / needs to be escaped by \\
        $result = str_replace('/', '\\\\/', $result);

        return $result;
    }

    /**
     * Replace the following reserved words (because part of the query language)
     * AND OR NOT
     *
     * @param  string $term Query term to replace
     * @return string Replaced query term
     * @link http://lucene.apache.org/java/2_4_0/queryparsersyntax.html#Boolean%20operators
     */
    public static function replaceBooleanWords($term)
    {
        $replacementMap = array('AND'=>'&&', 'OR'=>'||', 'NOT'=>'!');
        $result = strtr($term, $replacementMap);

        return $result;
    }

    /**
     * Converts a snake_case string to CamelCase
     *
     * For example: hello_world to HelloWorld
     *
     * @param  string $string snake_case string
     * @return string CamelCase string
     */
    public static function toCamelCase($string)
    {
        return str_replace(" ", "", ucwords(str_replace("_", " ", $string)));
    }

    /**
     * Converts a CamelCase string to snake_case
     *
     * For Example HelloWorld to hello_world
     *
     * @param  string $string CamelCase String to Convert
     * @return string SnakeCase string
     */
    public static function toSnakeCase($string)
    {
        $string = preg_replace('/([A-Z])/', '_$1', $string);

        return strtolower(substr($string,1));
    }

    /**
     * Converts given time to format: 1995-12-31T23:59:59Z
     *
     * This is the lucene date format
     *
     * @param  int    $date Date input (could be string etc.) -> must be supported by strtotime
     * @return string Converted date string
     */
    public static function convertDate($date)
    {
        if (is_int($date)) {
            $timestamp = $date;
        } else {
            $timestamp = strtotime($date);
        }
        $string =  date('Y-m-d\TH:i:s\Z', $timestamp);

        return $string;
    }

    /**
     * Tries to guess the name of the param, based on its class
     * Example: \Elastica\Filter\HasChildFilter => has_child
     *
     * @param string|object Class or Class name
     * @return string parameter name
     */
    public static function getParamName($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $parts = explode('\\', $class);
        $last  = array_pop($parts);
        $last  = preg_replace('/(Facet|Query|Filter)$/', '', $last);
        $name  = self::toSnakeCase($last);

        return $name;
    }

    /**
     * Converts Request to Curl console command
     *
     * @param Request $request
     * @return string
     */
    public static function convertRequestToCurlCommand(Request $request)
    {
        $message = 'curl -X' . strtoupper($request->getMethod()) . ' ';
        $message .= '\'http://' . $request->getConnection()->getHost() . ':' . $request->getConnection()->getPort() . '/';
        $message .= $request->getPath();

        $query = $request->getQuery();
        if (!empty($query)) {
            $message .= '?' . http_build_query($query);
        }

        $message .= '\'';

        $data = $request->getData();
        if (!empty($data)) {
            $message .= ' -d \'' . JSON::stringify($data) . '\'';
        }
        return $message;
    }
}
