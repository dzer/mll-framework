<?php

namespace Mll\Common;

/**
 * 常用工具类
 *
 * @package Mll\Common
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Common
{
    /**
     * 将对象转换为多维数组.
     *
     * @param object $value 传入对象
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
     * @param array $value 传入数组
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
     * @param int $size 字节
     *
     * @return string
     */
    public static function convert($size)
    {
        $unit = array('B', 'K', 'M', 'G', 'T', 'P');
        if ($size == 0) {
            return $size;
        }
        return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }

    /**
     * 格式化微妙时间
     *
     * @param null $mic 微妙
     *
     * @return float
     */
    public static function getMicroTime($mic = null)
    {
        $mic = $mic ? $mic : microtime();
        list($usec, $sec) = \explode(' ', $mic);

        return (float)$usec + (float)$sec;
    }

    /**
     * 返回一条BUG跟踪信息
     *
     * @param string $args 参数
     *
     * @return string
     */
    static function BugTrace($args = '') {
        $ret = debug_backtrace();
        krsort($ret, SORT_NUMERIC);
        $info = array();
        foreach($ret as $v) {
            $temp = explode('\\', $v['file']);
            $info[] = end($temp) . '::' . $v['class'] . $v['type'] . $v['function'] . "[{$v['line']}]";;
        }

        return implode(', ', $info);
    }

    /**
     * 严重数据库错误
     *
     * @param string $msg 描述
     * @param string $arg 参数
     *
     * @return array
     */
    static function dbErr($msg, $arg = array()) {
        $log = self::BugTrace() . print_r($arg, true);

        return ReturnMsg::err($msg.$log);
    }
}
