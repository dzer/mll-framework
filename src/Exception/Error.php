<?php

namespace Mll\Exception;

use Mll\Mll;
use Mll\Common\Common;
use Mll\Response\Response;

/**
 * 错误注册类
 *
 * @package Mll\Exception
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Error
{
    /**
     * 注册异常处理.
     */
    public static function register()
    {
        set_error_handler([__CLASS__, 'appError']);
        set_exception_handler([__CLASS__, 'appException']);
        register_shutdown_function([__CLASS__, 'appShutdown']);
    }

    /**
     * 异常处理.
     *
     * @param \Exception|\Throwable $e
     */
    public static function appException($e)
    {
        if (!$e instanceof \Exception) {
            $e = new ThrowableError($e);
        }
        self::getExceptionHandler()->report($e);
        /*while (ob_get_level() > 1) {
            ob_end_clean();
        }*/
        //ob_get_clean();

        $outData = self::getExceptionHandler()->render($e);
        // 获取并清空缓存
        $outData['echo'] = ob_get_clean();
        ob_start();
        // 判断请求头的content_type=json或者是ajax请求就返回json
        $headers = [];
        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
            $headers = $e->getHeaders();
        }
        if (!isset($statusCode)) {
            $statusCode = 500;
        }
        if ((isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json') || self::isAjax()) {
            $type = 'json';
        } else {
            extract((array)$outData);
            $tpl = Mll::app()->config->get('exception.template', __DIR__ . '/Template/mllExceptionTpl.php');
            if (file_exists($tpl)) {
                include $tpl;
            }
            // 获取并清空缓存
            $outData  = ob_get_clean();
            $type = 'view';
        }
        Response::create($outData, $type, $statusCode, $headers)->send();
        // 提高页面响应
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }

    /**
     * Error Handler.
     *
     * @param int $errNo 错误编号
     * @param int $errStr 详细错误信息
     * @param string $errFile 出错的文件
     * @param int $errLine 出错行号
     * @param array $errContext
     *
     * @throws ErrorException
     */
    public static function appError($errNo, $errStr, $errFile = '', $errLine = 0, $errContext = [])
    {
        $exception = new ErrorException($errNo, $errStr, $errFile, $errLine, $errContext);
        if (error_reporting() & $errNo) {
            // 将错误信息托管至 Mll\Exception\ErrorException
            throw $exception;
        } else {
            self::getExceptionHandler()->report($exception);
        }
    }

    /**
     * Shutdown Handler.
     */
    public static function appShutdown()
    {
        $errorMessage = '';
        if (!is_null($error = error_get_last()) && self::isFatal($error['type'])) {
            // 将错误信息托管至 Mll\Exception\ErrorException
            $exception = new ErrorException($error['type'], $error['message'], $error['file'], $error['line']);
            $codeMsg = self::getExceptionHandler()->getCodeMsg($error['type']);
            $errorMessage = "[{$error['type']}] {$codeMsg} {$error['message']}[{$error['file']}:{$error['line']}]";
            self::appException($exception);
        }
        $responseCode = http_response_code();
        $level = !empty($codeMsg) ? strtolower($codeMsg) : 'info';
        if ($responseCode > 400) {
            $level = 'error';
        }
        $xhprof_data = null;
        if (Mll::app()->config->get('xhprof.enable', false)
            && function_exists('xhprof_disable')
        ) {
            $xhprof_data = xhprof_disable();
        }
        $request = Mll::app()->request;

        Mll::app()->log->log($level, '请求', array(
            'traceId' => $request->getTraceId(true),
            'url' => $request->getUrl(true),
            'responseCode' => $responseCode,
            'method' => $request->method(true),
            'execTime' => Common::getMicroTime() - Common::getMicroTime(MLL_BEGIN_TIME),
            'timeout' => '',
            'useMemory' => memory_get_usage() - MLL_BEGIN_MEMORY,
            'useMemoryPeak' => memory_get_peak_usage(),
            'requestHeaders' => '', //$request->header(),
            'requestParams' => $request->param(),
            'errorMessage' => $errorMessage,
            'xhprof' => $xhprof_data,
        ), LOG_TYPE_FINISH);

        // 写入日志
        Mll::app()->log->save();
    }

    /**
     * 确定错误类型是否致命.
     *
     * @param int $type
     *
     * @return bool
     */
    protected static function isFatal($type)
    {
        return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE, E_USER_ERROR, E_RECOVERABLE_ERROR]);
    }

    /**
     * 获取异常处理程序的实例.
     *
     * @return Handle
     */
    public static function getExceptionHandler()
    {
        static $handle;
        if (!$handle) {
            // 异常处理handle
            $class = Mll::app()->config->get('exception.exception_handle');
            if ($class && class_exists($class) && is_subclass_of($class, '\\Mll\\Exception\\Handle')) {
                $handle = new $class();
            } else {
                $handle = new Handle();
            }
        }

        return $handle;
    }

    /**
     * 当前是否Ajax请求
     *
     * @return bool
     */
    public static function isAjax()
    {
        $value = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) : '';

        return 'xmlhttprequest' == $value ? true : false;
    }
}
