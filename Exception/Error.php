<?php

namespace Mll\Exception;

use Mll\Mll;

class Error
{
    /**
     * 注册异常处理.
     */
    public static function register()
    {
        error_reporting(E_ALL);
        set_error_handler([__CLASS__, 'appError']);
        set_exception_handler([__CLASS__, 'appException']);
        register_shutdown_function([__CLASS__, 'appShutdown']);
    }

    /**
     * Exception Handler.
     *
     * @param \Exception|\Throwable $e
     */
    public static function appException($e)
    {
        self::getExceptionHandler()->report($e);
        while (ob_get_level() > 1) {
            ob_end_clean();
        }
        ob_get_clean();

        $outData = self::getExceptionHandler()->render($e);
        // 获取并清空缓存
        //$outData['echo'] = ob_get_clean();
        ob_start();
        // 判断请求头的content_type=json或者是ajax请求就返回json
        if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json') {
            echo json_encode($outData);
        } else {
            extract((array) $outData);
            $tpl = Mll::app()->config->get('exception.template', __DIR__.'/Template/mllExceptionTpl.php');
            if (file_exists($tpl)) {
                include $tpl;
            }
            $content = ob_get_clean();
            http_response_code(500);
            echo $content;
        }
        // 提高页面响应
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }

    /**
     * Error Handler.
     *
     * @param int    $errNo      错误编号
     * @param int    $errStr     详细错误信息
     * @param string $errFile    出错的文件
     * @param int    $errLine    出错行号
     * @param array  $errContext
     *
     * @throws ErrorException
     */
    public static function appError($errNo, $errStr, $errFile = '', $errLine = 0, $errContext = [])
    {
        $exception = new ErrorException($errNo, $errStr, $errFile, $errLine, $errContext);
        if (error_reporting() & $errNo) {
            // 将错误信息托管至 think\exception\ErrorException
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
        if (!is_null($error = error_get_last()) && self::isFatal($error['type'])) {
            // 将错误信息托管至think\ErrorException
            $exception = new ErrorException($error['type'], $error['message'], $error['file'], $error['line']);

            self::appException($exception);
        }
        if (Mll::$debug) {
            $time = self::getMicroTime() - self::getMicroTime(MLL_BEGIN_TIME);
            $mem_use = memory_get_usage() - MLL_BEGIN_MEMORY;
            $run_id = 0;
            /*if (self::$xhprof) {
                $xhprof_data = \xhprof_disable();
                $xhprof_runs = new \XHProfRuns_Default();
                $run_id = $xhprof_runs->save_run($xhprof_data, 'random');
            }*/
            Mll::app()->log->info('debug', array(
                    'exec_time: ' . $time,
                    'use_memory: ' . self::convert($mem_use),
                    'run_id: ' . $run_id,
                    'url: ' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . $_SERVER['REQUEST_URI'],
                    )
            );
        }
        // 写入日志
        Mll::app()->log->save();
    }

    private static function convert($size)
    {
        $unit = array('B', 'K', 'M', 'G', 'T', 'P');
        return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }

    public static function getMicroTime($mic = null)
    {
        $mic = $mic ?  $mic : microtime();
        list($usec, $sec) = \explode(" ", $mic);
        return ((float)$usec + (float)$sec);
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
        return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }

    /**
     * Get an instance of the exception handler.
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
}
