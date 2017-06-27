<?php

namespace Mll\Exception;

use Mll\Mll;
use Exception;

/**
 * 异常处理.
 *
 * @package Mll\Exception
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Handle
{
    /**
     * 忽略的异常.
     *
     * @var array
     */
    protected $ignoreReport = [

    ];

    /**
     * 报告和记录异常.
     *
     * @param \Exception $exception
     * @return string
     */
    public function report(Exception $exception)
    {
        if (!$this->isIgnoreReport($exception)) {
            // 收集异常数据
            $isFatal = in_array($this->getCode($exception), [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
            $data = [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'message' => ($isFatal ? 'fatal error:' : '') . $this->getMessage($exception),
                'code' => $this->getCode($exception),
            ];
            $log = "[{$data['code']}]{$data['message']}[{$data['file']}:{$data['line']}]";
            Mll::app()->log->error($log, [], LOG_TYPE_SYSTEM);
            return $log;
        }
    }

    /**
     * 判断是否忽略异常.
     *
     * @param Exception $exception
     *
     * @return bool
     */
    protected function isIgnoreReport(Exception $exception)
    {
        foreach ($this->ignoreReport as $class) {
            if ($exception instanceof $class) {
                return true;
            }
        }

        return false;
    }

    /**
     * 将异常呈现为HTTP响应.
     *
     * @param \Exception $e
     *
     * @return array
     */
    public function render(Exception $e)
    {
        return $this->convertExceptionToResponse($e);
    }

    /**
     * 将异常格式化响应数据.
     *
     * @param Exception $exception
     *
     * @return mixed
     */
    protected function convertExceptionToResponse(Exception $exception)
    {
        // 收集异常数据
        if (Mll::$debug) {
            // 调试模式，获取详细的错误信息
            $data = [
                'name' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'message' => $this->getMessage($exception),
                'trace' => $exception->getTrace(),
                'code' => $this->getCode($exception),
                'source' => $this->getSourceCode($exception),
                'tables' => [
                    'GET Data' => $_GET,
                    'POST Data' => $_POST,
                    'Files' => $_FILES,
                    'Cookies' => $_COOKIE,
                    'Session' => isset($_SESSION) ? $_SESSION : [],
                    'Server/Request Data' => $_SERVER,
                    'Environment Variables' => $_ENV,
                    'Mll Constants' => $this->getConst(),
                ],
            ];
        } else {
            // 部署模式仅显示 Code 和 Message
            $data = [
                'code' => $this->getCode($exception),
                'message' => $this->getMessage($exception),
            ];

            if (!Mll::app()->config->get('exception.show_error_msg', true) || MLL_ENV_PROD) {
                //在header头定义错误信息

                // 不显示详细错误信息
                $data['message'] = Mll::app()->config->get('exception.error_message', '系统繁忙,请稍后再试');
            }
        }

        return $data;
    }

    /**
     * 获取错误编码
     * ErrorException则使用错误级别作为错误编码
     *
     * @param \Exception $exception
     *
     * @return int 错误编码
     */
    protected function getCode(Exception $exception)
    {
        $code = $exception->getCode();
        if (!$code && $exception instanceof ErrorException) {
            $code = $exception->getSeverity();
        }

        return $code;
    }

    /**
     * 获取错误信息
     * ErrorException则使用错误级别作为错误编码
     *
     * @param \Exception $exception
     *
     * @return string 错误信息
     */
    protected function getMessage(Exception $exception)
    {
        $message = $exception->getMessage();

        return $message;
    }

    /**
     * 获取出错文件内容
     * 获取错误的前9行和后9行.
     *
     * @param \Exception $exception
     *
     * @return array 错误文件内容
     */
    protected function getSourceCode(Exception $exception)
    {
        // 读取前9行和后9行
        $line = $exception->getLine();
        $first = ($line - 9 > 0) ? $line - 9 : 1;

        try {
            $contents = file($exception->getFile());
            $source = [
                'first' => $first,
                'source' => array_slice($contents, $first - 1, 19),
            ];
        } catch (Exception $e) {
            $source = [];
        }

        return $source;
    }

    /**
     * 获取常量列表.
     *
     * @return array 常量列表
     */
    private static function getConst()
    {
        return get_defined_constants(true)['user'];
    }
}
