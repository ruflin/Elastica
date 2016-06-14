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
     * @param string $json JSON string to parse
     *
     * @return array PHP array representation of JSON string
     */
    public static function parse(/* inherit from json_decode */)
    {
        // extract arguments
        $args = func_get_args();

        // default to decoding into an assoc array
        if (sizeof($args) === 1) {
            $args[] = true;
        }

        // run decode
        $array = call_user_func_array('json_decode', $args);

        // turn errors into exceptions for easier catching
        $error = json_last_error();
        if ($error !== JSON_ERROR_NONE) {
            throw new JSONParseException($error);
        }

        // output
        return $array;
    }

    /**
     * Convert input to JSON string with standard options.
     *
     * @link http://php.net/manual/en/function.json-encode.php
     *
     * @param  mixed check args for PHP function json_encode
     *
     * @return string Valid JSON representation of $input
     */
    public static function stringify(/* inherit from json_encode */)
    {
        // extract arguments
        $args = func_get_args();

        // allow special options value for Elasticsearch compatibility
        if (sizeof($args) > 1 && $args[1] === 'JSON_ELASTICSEARCH') {
            // Use built in JSON constants if available (php >= 5.4)
            $args[1] = defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 256;
        }

        // run encode and output
        return call_user_func_array('json_encode', $args);
    }
}
