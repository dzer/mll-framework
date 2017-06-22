<?php

namespace Mll\Exception;

/**
 * 错误异常
 *
 * @package Mll\Exception
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class ErrorException extends \Exception
{
    /**
     * 用于保存错误级别.
     *
     * @var int
     */
    protected $severity;

    /**
     * 错误异常构造函数.
     *
     * @param int    $severity 错误级别
     * @param string $message  错误详细信息
     * @param string $file     出错文件路径
     * @param int    $line     出错行号
     * @param array  $context  错误上下文，会包含错误触发处作用域内所有变量的数组
     */
    public function __construct($severity, $message, $file, $line, array $context = [])
    {
        $this->severity = $severity;
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;
        $this->code = 0;
    }

    /**
     * 获取错误级别.
     *
     * @return int 错误级别
     */
    final public function getSeverity()
    {
        return $this->severity;
    }
}
