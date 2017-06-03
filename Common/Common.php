<?php

namespace Mll\Common;

class Common
{
    /**
     * 将对象转换为多维数组.
     **/
    public static function objectToArray($d)
    {
        if (is_object($d)) {
            // Gets the properties of the given object
            // with get_object_vars function
            $d = get_object_vars($d);
        }

        if (is_array($d)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            return array_map(__METHOD__, $d);
        } else {
            // Return array
            return $d;
        }
    }

    /**
     * 将多维数组转换为对象
     **/
    public static function arrayToObject($d)
    {
        if (is_array($d)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            return (object)array_map(__METHOD__, $d);
        } else {
            // Return object
            return $d;
        }
    }
}
