<?php

namespace Mll\Log;

interface ILog
{
    /**
     * 系统不可用.
     *
     * @param string $message
     * @param array $context
     * @param string $type
     */
    public function emergency($message, array $context = array(), $type = null);

    /**
     *  **必须** 立刻采取行动.
     *
     * 例如：在整个网站都垮掉了、数据库不可用了或者其他的情况下， **应该** 发送一条警报短信把你叫醒。
     *
     * @param string $message
     * @param array $context
     * @param string $type
     */
    public function alert($message, array $context = array(), $type = null);

    /**
     * 紧急情况.
     *
     * 例如：程序组件不可用或者出现非预期的异常。
     *
     * @param string $message
     * @param array $context
     * @param string $type
     */
    public function critical($message, array $context = array(), $type = null);

    /**
     * 运行时出现的错误，不需要立刻采取行动，但必须记录下来以备检测。
     *
     * @param string $message
     * @param array $context
     * @param string $type
     */
    public function error($message, array $context = array(), $type = null);

    /**
     * 出现非错误性的异常。
     *
     * 例如：使用了被弃用的API、错误地使用了API或者非预想的不必要错误。
     *
     * @param string $message
     * @param array $context
     * @param string $type
     */
    public function warning($message, array $context = array(), $type = null);

    /**
     * 一般性重要的事件。
     *
     * @param string $message
     * @param array $context
     * @param string $type
     */
    public function notice($message, array $context = array(), $type = null);

    /**
     * 重要事件.
     *
     * 例如：用户登录和SQL记录。
     *
     * @param string $message
     * @param array $context
     * @param string $type
     */
    public function info($message, array $context = array(), $type = null);

    /**
     * debug 详情.
     *
     * @param string $message
     * @param array $context
     * @param string $type
     */
    public function debug($message, array $context = array(), $type = null);

    /**
     * 任意等级的日志记录.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @param string $type
     */
    public function log($level, $message, array $context = array(), $type = null);

    /**
     * 保存日志.
     *
     * @return bool
     */
    public function save();
}
