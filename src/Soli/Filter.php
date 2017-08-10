<?php

namespace Soli;

/**
 * 过滤器
 */
class Filter
{
    /**
     * 使用对应过滤标识进行过滤
     *
     * @param mixed $value
     * @param string $filter
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function sanitize($value, $filter)
    {
        switch ($filter) {
            case 'int':
                return filter_var($value, FILTER_SANITIZE_NUMBER_INT);

            case 'int!':
                return intval($value);

            case 'absint':
                return abs(intval($value));

            case 'string':
                // 注意不要使用 string 过滤 JSON 数据，如: {"data":123} 会被过滤为 {&#34;data&#34;:123}
                return filter_var($value, FILTER_SANITIZE_STRING);

            case 'float':
                return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, ['flags' => FILTER_FLAG_ALLOW_FRACTION]);

            case 'float!':
                return doubleval($value);

            case 'alphanum':
                return preg_replace('/[^A-Za-z0-9]/', '', $value);

            case 'trim':
                return trim($value);

            case 'striptags':
                return strip_tags($value);

            case 'lower':
                return mb_strtolower($value);

            case 'upper':
                return mb_strtoupper($value);

            case 'email':
                return filter_var($value, FILTER_SANITIZE_EMAIL);

            case 'url':
                return filter_var($value, FILTER_SANITIZE_URL);

            case 'special_chars':
                return filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);

            default:
                throw new \InvalidArgumentException("Sanitize filter $filter is not supported");
        }
    }
}
