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
     * @throws \Soli\Exception
     */
    public function sanitize($value, $filter)
    {
        switch ($filter) {
            case 'email':
                return filter_var($value, FILTER_SANITIZE_EMAIL);

            case 'int':
                return filter_var($value, FILTER_SANITIZE_NUMBER_INT);

            case 'int!':
                return intval($value);

            case 'absint':
                return abs(intval($value));

            case 'string':
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

            default:
                throw new Exception("Sanitize filter $filter is not supported");
        }
    }
}
