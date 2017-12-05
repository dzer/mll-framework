<?php

namespace Mll\Exception;

/**
 * 错误异常
 *
 * @package Mll\Exception
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class ThrowableError extends \ErrorException
{
    public function __construct(\Throwable $e)
    {

        if ($e instanceof \ParseError) {
            $message  = 'Parse error: ' . $e->getMessage();
            $severity = E_PARSE;
        } elseif ($e instanceof \TypeError) {
            $message  = 'Type error: ' . $e->getMessage();
            $severity = E_RECOVERABLE_ERROR;
        } else {
            $message  = 'Fatal error: ' . $e->getMessage();
            $severity = E_ERROR;
        }

        parent::__construct(
            $message,
            $e->getCode(),
            $severity,
            $e->getFile(),
            $e->getLine()
        );
        $this->setTrace($e->getTrace());
    }

    protected function setTrace($trace)
    {
        $traceReflector = new \ReflectionProperty('Exception', 'trace');
        $traceReflector->setAccessible(true);
        $traceReflector->setValue($this, $trace);
    }
}
