<?php

namespace Mll\Log;

/**
 * 日志基础类
 *
 * @package Mll\Log
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
abstract class Base
{
    const EMERGENCY = 'emergency';      //系统不可用
    const ALERT = 'alert';              //必须立刻采取行动
    const CRITICAL = 'critical';        //紧急情况
    const ERROR = 'error';              //运行时出现的错误
    const WARNING = 'warning';          //出现非错误性的异常
    const NOTICE = 'notice';            //一般性重要的事件
    const INFO = 'info';                //重要事件
    const DEBUG = 'debug';              //debug记录

    /**
     * 系统不可用
     *
     * @param string $message 消息
     * @param array $context 内容
     * @param string $type 类型
     * @return void
     */
    public function emergency($message, array $context = array(), $type = null)
    {
        $this->log(self::EMERGENCY, $message, $context, $type);
    }

    /**
     *  **必须** 立刻采取行动
     *
     * 例如：在整个网站都垮掉了、数据库不可用了或者其他的情况下， **应该** 发送一条警报短信把你叫醒。
     *
     * @param string $message 消息
     * @param array $context 内容
     * @param string $type 类型
     * @return void
     */
    public function alert($message, array $context = array(), $type = null)
    {
        $this->log(self::ALERT, $message, $context, $type);
    }

    /**
     * 紧急情况
     *
     * 例如：程序组件不可用或者出现非预期的异常。
     *
     * @param string $message 消息
     * @param array $context 内容
     * @param string $type 类型
     * @return void
     */
    public function critical($message, array $context = array(), $type = null)
    {
        $this->log(self::CRITICAL, $message, $context, $type);
    }

    /**
     * 运行时出现的错误，不需要立刻采取行动，但必须记录下来以备检测。
     *
     * @param string $message 消息
     * @param array $context 内容
     * @param string $type 类型
     * @return void
     */
    public function error($message, array $context = array(), $type = null)
    {
        $this->log(self::ERROR, $message, $context, $type);
    }

    /**
     * 出现非错误性的异常。
     *
     * 例如：使用了被弃用的API、错误地使用了API或者非预想的不必要错误。
     *
     * @param string $message 消息
     * @param array $context 内容
     * @param string $type 类型
     * @return void
     */
    public function warning($message, array $context = array(), $type = null)
    {
        $this->log(self::WARNING, $message, $context, $type);
    }

    /**
     * 一般性重要的事件。
     *
     * @param string $message 消息
     * @param array $context 内容
     * @param string $type 类型
     * @return void
     */
    public function notice($message, array $context = array(), $type = null)
    {
        $this->log(self::NOTICE, $message, $context, $type);
    }

    /**
     * 重要事件
     *
     * 例如：用户登录和SQL记录。
     *
     * @param string $message 消息
     * @param array $context 内容
     * @param string $type 类型
     * @return void
     */
    public function info($message, array $context = array(), $type = null)
    {
        $this->log(self::INFO, $message, $context, $type);
    }

    /**
     * debug 详情
     *
     * @param string $message 消息
     * @param array $context 内容
     * @param string $type 类型
     * @return void
     */
    public function debug($message, array $context = array(), $type = null)
    {
        $this->log(self::DEBUG, $message, $context, $type);
    }

    /**
     * 任意等级的日志记录
     *
     * @param string $message 消息
     * @param array $context 内容
     * @param string $type 类型
     */
    abstract public function log($level, $message, array $context = array(), $type = null);
}
