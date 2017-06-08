<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Mll\Log\Driver;

use Mll\Mll;
use Mll\Log\ILog;

/**
 * Class Log
 * @package think
 *
 * @method void log($msg) static
 * @method void error($msg) static
 * @method void info($msg) static
 * @method void sql($msg) static
 * @method void notice($msg) static
 * @method void alert($msg) static
 */
class Log implements ILog
{
    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';
    const SQL = 'sql';

    // 日志信息
    protected static $log = [];
    // 配置参数
    protected static $config = [];
    // 日志类型
    protected static $type = ['log', 'error', 'info', 'sql', 'notice', 'alert', 'debug'];
    // 日志写入驱动
    protected static $driver;

    // 当前日志授权key
    protected static $key;

    public function __construct()
    {
        Mll::$debug && $this->info('[ LOG ] INIT ' . $type, 'info');
    }

    /**
     * 日志初始化
     * @param array $config
     */
    public function init($config = [])
    {
        $type         = isset($config['type']) ? $config['type'] : 'File';
        $class        = false !== strpos($type, '\\') ? $type : '\\think\\log\\driver\\' . ucwords($type);
        self::$config = $config;
        unset($config['type']);
        if (class_exists($class)) {
            self::$driver = new $class($config);
        } else {
            throw new ClassNotFoundException('class not exists:' . $class, $class);
        }
        // 记录初始化信息
        Mll::$debug && Log::record('[ LOG ] INIT ' . $type, 'info');
    }

    /**
     * 记录调试信息
     * @param mixed  $msg  调试信息
     * @param string $type 信息类型
     * @return void
     */
    public static function record($msg, $type = 'log')
    {
        self::$log[$type][] = $msg;
        if (IS_CLI && count(self::$log[$type]) > 100) {
            // 命令行下面日志写入改进
            self::save();
        }
    }

    /**
     * 获取日志信息
     * @param string $type 信息类型
     * @return array
     */
    public static function getLog($type = '')
    {
        return $type ? self::$log[$type] : self::$log;
    }


    /**
     * 清空日志信息
     * @return void
     */
    public function clear()
    {
        self::$log = [];
    }

    /**
     * 当前日志记录的授权key
     * @param string $key 授权key
     * @return void
     */
    public function key($key)
    {
        self::$key = $key;
    }

    /**
     * 检查日志写入权限
     * @param array $config 当前日志配置参数
     * @return bool
     */
    public function check($config)
    {
        if (self::$key && !empty($config['allow_key']) && !in_array(self::$key, $config['allow_key'])) {
            return false;
        }
        return true;
    }

    /**
     * 保存调试信息
     * @return bool
     */
    public function save()
    {
        if (!empty(self::$log)) {
            if (is_null(self::$driver)) {
                self::init(Config::get('log'));
            }

            if (!self::check(self::$config)) {
                // 检测日志写入权限
                return false;
            }

            if (empty(self::$config['level'])) {
                // 获取全部日志
                $log = self::$log;
                if (!App::$debug && isset($log['debug'])) {
                    unset($log['debug']);
                }
            } else {
                // 记录允许级别
                $log = [];
                foreach (self::$config['level'] as $level) {
                    if (isset(self::$log[$level])) {
                        $log[$level] = self::$log[$level];
                    }
                }
            }

            $result = self::$driver->save($log);
            if ($result) {
                self::$log = [];
            }

            return $result;
        }
        return true;
    }

    /**
     * 实时写入日志信息 并支持行为
     * @param mixed $msg 调试信息
     * @param string $type 信息类型
     * @param bool $force 是否强制写入
     * @return bool
     */
    public static function write($msg, $type = 'log', $force = false)
    {
        // 封装日志信息
        if (true === $force || empty(self::$config['level'])) {
            $log[$type][] = $msg;
        } elseif (in_array($type, self::$config['level'])) {
            $log[$type][] = $msg;
        } else {
            return false;
        }

        // 监听log_write
        Hook::listen('log_write', $log);
        if (is_null(self::$driver)) {
            self::init(Config::get('log'));
        }
        // 写入日志
        return self::$driver->save($log, false);
    }

    /**
     * 静态调用
     * @param $method
     * @param $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        if (in_array($method, self::$type)) {
            array_push($args, $method);
            return call_user_func_array('\\think\\Log::record', $args);
        }
    }

    /**
     * 系统不可用
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = array()){}

    /**
     *  **必须** 立刻采取行动
     *
     * 例如：在整个网站都垮掉了、数据库不可用了或者其他的情况下， **应该** 发送一条警报短信把你叫醒。
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = array()){}

    /**
     * 紧急情况
     *
     * 例如：程序组件不可用或者出现非预期的异常。
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = array()){}

    /**
     * 运行时出现的错误，不需要立刻采取行动，但必须记录下来以备检测。
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array()){}

    /**
     * 出现非错误性的异常。
     *
     * 例如：使用了被弃用的API、错误地使用了API或者非预想的不必要错误。
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = array()){}

    /**
     * 一般性重要的事件。
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array()){}

    /**
     * 重要事件
     *
     * 例如：用户登录和SQL记录。
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array()){}

    /**
     * debug 详情
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = array()){}

    /**
     * 任意等级的日志记录
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array()){}

}
