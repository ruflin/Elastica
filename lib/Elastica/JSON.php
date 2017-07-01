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
     * @link http://php.net/manual/en/function.json-decode.php
     * @link http://php.net/manual/en/function.json-last-error.php
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
        $args = func_get_args();

        // default to decoding into an assoc array
        if (count($args) === 1) {
            $args[] = true;
        }

        // run decode
        $array = call_user_func_array('json_decode', $args);

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
     * @link http://php.net/manual/en/function.json-encode.php
     * @link http://php.net/manual/en/function.json-last-error.php
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
        $args = func_get_args();

        // run encode and output
        $string = call_user_func_array('json_encode', $args);

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
     * @link http://php.net/manual/en/function.json-last-error.php
     * @link http://php.net/manual/en/function.json-last-error-msg.php
     * @link https://github.com/php/php-src/blob/master/ext/json/json.c#L308
     *
     * @return string
     */
    private static function getJsonLastErrorMsg()
    {
        return JSON_ERROR_NONE !== json_last_error() ? json_last_error_msg() : false;
    }
}
