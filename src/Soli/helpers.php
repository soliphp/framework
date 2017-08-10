<?php

if (!function_exists('camelize')) {
    /**
     * Converts strings to camelize style
     *
     * <code>
     * echo camelize('coco_bongo'); // CocoBongo
     * echo camelize('co_co-bon_go', '-'); // Co_coBon_go
     * echo camelize('co_co-bon_go', '_-'); // CoCoBonGo
     * </code>
     *
     * @param string $str
     * @param string $delimiter
     * @return string
     */
    function camelize($str, $delimiter = '_')
    {
        $str = ucwords(str_replace(str_split($delimiter), ' ', $str));

        return str_replace(' ', '', $str);
    }
}

if (!function_exists('uncamelize')) {
    /**
     * Uncamelize strings which are camelized
     *
     * <code>
     * echo uncamelize('CocoBongo'); // coco_bongo
     * echo uncamelize('CocoBongo', '-'); // coco-bongo
     * </code>
     *
     * @param string $str
     * @param string $delimiter
     * @return string
     */
    function uncamelize($str, $delimiter = '_')
    {
        if (!ctype_lower($str)) {
            $str = preg_replace('/\s+/u', '', $str);
            $str = lower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $str));
        }

        return $str;
    }
}

if (!function_exists('lower')) {
    /**
     * Convert the given string to lower-case.
     *
     * <code>
     * echo lower('HELLO'); // hello
     * </code>
     *
     * @param string $str
     * @return string
     */
    function lower($str)
    {
        return mb_strtolower($str, 'UTF-8');
    }
}

if (!function_exists('upper')) {
    /**
     * Convert the given string to upper-case.
     *
     * <code>
     * echo upper('hello'); // HELLO
     * </code>
     *
     * @param string $str
     * @return string
     */
    function upper($str)
    {
        return mb_strtoupper($str, 'UTF-8');
    }
}

if (!function_exists('starts_with')) {
    /**
     * Check if a string starts with a given string
     *
     * <code>
     * echo starts_with('Hello', 'He'); // true
     * echo starts_with('Hello', 'he'); // false
     * </code>
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    function starts_with($haystack, $needle)
    {
        return (string)$needle === substr($haystack, 0, strlen($needle));
    }
}

if (!function_exists('ends_with')) {
    /**
     * Check if a string ends with a given string
     *
     * <code>
     * echo ends_with('Hello', 'llo'); // true
     * echo ends_with('Hello', 'LLO'); // false
     * </code>
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    function ends_with($haystack, $needle)
    {
        return (string)$needle === substr($haystack, -strlen($needle));
    }
}

if (!function_exists('contains')) {
    /**
     * Determine if a given string contains a given substring.
     *
     * <code>
     * echo contains('Hello', 'ell'); // true
     * echo contains('Hello', 'hll'); // false
     * </code>
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    function contains($haystack, $needle)
    {
        return mb_strpos($haystack, $needle) !== false;
    }
}

if (!function_exists('is_json')) {
    /**
     * Check if a string is JSON
     *
     * <code>
     * echo is_json('{"data":123}'); // true
     * echo is_json('{data:123}'); // false
     * </code>
     *
     * @param string $str
     * @return bool
     */
    function is_json($str)
    {
        if (is_string($str)) {
            json_decode($str);
            return (json_last_error() == JSON_ERROR_NONE);
        }
        return false;
    }
}
