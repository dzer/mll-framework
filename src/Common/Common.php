<?php

namespace Mll\Common;
use Mll\Mll;

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
        return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2)
            . ' ' . $unit[$i];
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
    static function BugTrace($args = '')
    {
        $ret = debug_backtrace();
        krsort($ret, SORT_NUMERIC);
        $info = array();
        foreach ($ret as $v) {
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
    static function dbErr($msg, $arg = array())
    {
        $log = self::BugTrace() . print_r($arg, true);

        return ReturnMsg::err($msg . $log);
    }

    /**
     * 递归方式的对变量中的特殊字符进行转义
     *
     * @param $value
     * @return array|mixed|string
     * @throws \Exception
     */
    public static function addslashes_deep($value)
    {
        if (empty($value)) {
            return $value;
        } else {
            $value = str_ireplace('script>', ' ', $value);
            $value = str_replace('<', '&lt;', $value);
            $value = str_replace('>', '&gt;', $value);
            if (preg_match('/SELECT.*FROM|UPDATE.*SET|DELETE.*FROM|UNION.*SELECT|SLEEP\s*\(|DROP\s*TABLE|'
                . 'DROP\s*DATABASE|CREATE\s*TABLE|CREATE\s*DATABASE|TRUNCATE|ALERT\s*TABLE|ALERT\s*DATABASE'
                . '|SHOW\s*TABLE|SHOW\s*DATABASE|INSERT.*INTO|REPLACE.*INTO|BENCHMARK\s*\(/i', $value)) {
                throw new \Exception('invalid params');
            }
            return addslashes($value);
        }
    }

    /**
     *  返回缩略图路径
     *
     * @param string $path
     * @param string $width
     * @param string $height
     * @param int $mode
     * @param int $percent
     * @return bool|mixed|string
     * @throws \Exception
     */
    public static function mllThumb($path = '', $width = '', $height = '', $mode = 1, $percent = 92)
    {
        if (empty($path)) {
            return false;
        }
        if (empty($width) && empty($height)) {
            return $path;
        }
        if (strlen($path) >= 100 && strpos($path, '/small/')) {
            return $path;
        }
        $path = preg_replace('/(.*?)(.com)\//', '', $path);
        $img_servers = Mll::app()->config->get('source_server_host');
        if (empty($img_servers)) {
            throw new \Exception('resource server is empty');
        }
        $img = $img_servers[0];

        $path = $img . '/' . $path;
        if ($percent == 92) {
            $encrypt = md5($width . $height . $path . '8@#ccmll_$%Z@#4a0cz!@#');
        } else {
            $encrypt = md5($width . $height . $path . $percent . '8@#ccmll_$%Z@#4a0cz!@#');
            $path = $path . '?percent=' . $percent;
        }
        if (!$mode) {
            $mode = 1;
        }

        $dir = substr(crc32($path . $width . $height . $mode), 0, 3);
        $pass = self::transCrypt($path, 'encode');
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        return $img . '/images/small/' . $dir . '/width/' . $width . '/height/' . $height . '/mode/'
            . $mode . '/encrypt/' . $encrypt . '/path/' . $pass . '.' . $ext;
    }

    /**
     * 可逆加密
     *
     * @param $date
     * @param string $mode
     * @return mixed|string
     */
    public static function transCrypt($date, $mode = 'encode')
    {
        //用MD5哈希生成一个密钥，注意加密和解密的密钥必须统一
        $key = md5('sdf*sf2e1dfv(^$');

        $cipher = 'aes-256-ecb';
        $iv_len = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($iv_len);
        if ($mode == 'encode') {
            $pass_crypt = openssl_encrypt($date, $cipher, $key, 0, $iv);
            return str_replace('/', '_', $pass_crypt);
        }

        if ($mode == 'decode') {
            $date = str_replace('_', '/', $date);
            return openssl_decrypt($date, $cipher, $key, 0, $iv);
        }

        return null;
    }

}
