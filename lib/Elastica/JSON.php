<?php

namespace Elastica;

use Elastica\Exception\JSONParseException;

/**
 * Elastica JSON tools.
 */
class JSON
{
    /**
     * Parse JSON string to an array.
     *
     * @see http://php.net/manual/en/function.json-decode.php
     * @see http://php.net/manual/en/function.json-last-error.php
     *
     * @param string $args,... JSON string to parse
     *
     * @throws JSONParseException
     *
     * @return array PHP array representation of JSON string
     */
    public static function parse($args/* inherit from json_decode */)
    {
        // extract arguments
        $args = \func_get_args();

        // default to decoding into an assoc array
        if (1 === \count($args)) {
            $args[] = true;
        }

        // run decode
        $array = \call_user_func_array('json_decode', $args);

        // turn errors into exceptions for easier catching
        if ($error = self::getJsonLastErrorMsg()) {
            throw new JSONParseException($error);
        }

        // output
        return $array;
    }

    /**
     * Convert input to JSON string with standard options.
     *
     * @see http://php.net/manual/en/function.json-encode.php
     * @see http://php.net/manual/en/function.json-last-error.php
     *
     * @param mixed $args,... Target to stringify
     *
     * @throws JSONParseException
     *
     * @return string Valid JSON representation of $input
     */
    public static function stringify($args/* inherit from json_encode */)
    {
        // extract arguments
        $args = \func_get_args();

        // set defaults
        isset($args[1]) ? $args[1] |= JSON_PRESERVE_ZERO_FRACTION : $args[1] = JSON_PRESERVE_ZERO_FRACTION;

        // run encode and output
        $string = \call_user_func_array('json_encode', $args);

        // turn errors into exceptions for easier catching
        if ($error = self::getJsonLastErrorMsg()) {
            throw new JSONParseException($error);
        }

        // output
        return $string;
    }

    /**
     * Get Json Last Error.
     *
     * @see http://php.net/manual/en/function.json-last-error.php
     * @see http://php.net/manual/en/function.json-last-error-msg.php
     * @see https://github.com/php/php-src/blob/master/ext/json/json.c#L308
     *
     * @return string
     */
    private static function getJsonLastErrorMsg()
    {
        return JSON_ERROR_NONE !== \json_last_error() ? \json_last_error_msg() : false;
    }
}
