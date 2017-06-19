<?php

namespace Mll\Common;

class Common
{
    /**
     * 将对象转换为多维数组.
     *
     * @param object $value
     *
     * @return array
     */
    public static function objectToArray($value)
    {
        if (is_object($value)) {
            // Gets the properties of the given object
            // with get_object_vars function
            $value = get_object_vars($value);
        }

        if (is_array($value)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            return array_map(__METHOD__, $value);
        } else {
            // Return array
            return $value;
        }
    }

    /**
     *  将多维数组转换为对象
     *
     * @param array $value
     *
     * @return object
     */
    public static function arrayToObject($value)
    {
        if (is_array($value)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            return (object)array_map(__METHOD__, $value);
        } else {
            // Return object
            return (object)$value;
        }
    }

    /**
     * 将字节转换成相应的单位.
     *
     * @param $size
     *
     * @return string
     */
    public static function convert($size)
    {
        $unit = array('B', 'K', 'M', 'G', 'T', 'P');

        return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }

    /**
     * getMicroTime.
     *
     * @param null $mic
     *
     * @return float
     */
    public static function getMicroTime($mic = null)
    {
        $mic = $mic ? $mic : microtime();
        list($usec, $sec) = \explode(' ', $mic);

        return (float)$usec + (float)$sec;
    }
}
