<?php

namespace Mll\Response;

use Mll\Common\Common;
use Mll\Mll;

/**
 * 响应类.
 *
 * @author Xu Dong <d20053140@gmail.com>
 *
 * @since 1.0
 */
class Response
{
    // 原始数据
    protected $data;

    // 当前的contentType
    protected $contentType = '';

    // 字符集
    protected $charset = 'utf-8';

    //状态
    protected $code = 200;

    //响应类型
    protected $type = 'html';

    // 输出参数
    protected $options = [];

    // header参数
    public $header = [];

    public $content = null;

    const RESPONSE_TIME_KEY = 'x-run-Time';

    /**
     * 架构函数.
     *
     * @param mixed $data 输出数据
     * @param int $code http状态码
     * @param array $header 响应头
     * @param array $options 输出参数
     */
    public function __construct()
    {}

    public function create($data = [], $type = 'html', $code = 200, $header = [], $options = []){
        $this->data = $data;
        $this->type = $type;
        $this->code = $code;
        if (!empty($header)) {
            $this->header = array_merge($this->header, $header);
        }
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        return $this;
    }

    /**
     * 发送数据到客户端.
     *
     * @return mixed|string|null 响应数据
     *
     * @throws \InvalidArgumentException
     */
    public function send($data = null, $type = '')
    {
        $request = Mll::app()->request;

        if ($data !== null) {
            $this->data = $data;
        }

        if (!empty($type)) {
            $this->type = $type;
        }

        // 处理输出数据
        $data = $this->getContent($this->type);

        // header contentType
        $this->contentType($this->contentType, $this->charset);

        $this->header['x-request-id'] = $request->getRequestId();
        $this->header[self::RESPONSE_TIME_KEY] = microtime(true) - $request->getRequestTime();

        if (SERVER_MODEL == 'SwooleHttp' && isset(Mll::app()->swooleResponse)) {
            $swooleResponse = Mll::app()->swooleResponse;
            // 发送状态码
            $swooleResponse->status(intval($this->code));
            // 发送头部信息
            if (!empty($this->header)) {
                foreach ($this->header as $name => $val) {
                    $swooleResponse->header($name, $val);
                }
            }
        } else {
            if (!headers_sent() && !empty($this->header)) {
                // 发送状态码
                http_response_code(intval($this->code));

                // 发送头部信息
                foreach ($this->header as $name => $val) {
                    header($name . ':' . $val);
                }
            }
        }
        if (ob_get_level() == 0) {
            ob_start();
        }
        echo $data;

        if (SERVER_MODEL == 'SwooleHttp' || SERVER_MODEL == 'Rpc') {
            return ob_get_clean();
        }

        if (function_exists('fastcgi_finish_request')) {
            // 提高页面响应
            fastcgi_finish_request();
        }
        return null;

    }

    public function json($data)
    {
        $this->contentType = empty($this->contentType) ? 'application/json' : $this->contentType;
        $this->options['json_encode_param'] = JSON_UNESCAPED_UNICODE;
        try {
            // 返回JSON数据格式到客户端 包含状态信息
            $data = json_encode($data, $this->options['json_encode_param']);

            if ($data === false) {
                throw new \InvalidArgumentException(json_last_error_msg());
            }

            return $data;
        } catch (\Exception $e) {
            if ($e->getPrevious()) {
                throw $e->getPrevious();
            }
            throw $e;
        }
    }

    public function html($data)
    {
        $this->contentType = empty($this->contentType) ? 'text/html' : $this->contentType;
        return $data;
    }

    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * 处理数据.
     *
     * @param mixed $data 要处理的数据
     *
     * @return mixed
     */
    protected function output($data)
    {
        return $data;
    }

    /**
     * 输出的参数.
     *
     * @param mixed $options 输出参数
     *
     * @return $this
     */
    public function options($options = [])
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * 输出数据设置.
     *
     * @param mixed $data 输出数据
     *
     * @return $this
     */
    public function data($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * 设置响应头.
     *
     * @param string|array $name 参数名
     * @param string $value 参数值
     *
     * @return $this
     */
    public function header($name, $value = null)
    {
        if (is_array($name)) {
            $this->header = array_merge($this->header, $name);
        } else {
            $this->header[$name] = $value;
        }

        return $this;
    }

    /**
     * 设置页面输出内容.
     *
     * @param $content
     *
     * @return $this
     */
    public function content($content)
    {
        if (null !== $content && !is_string($content) && !is_numeric($content) && !is_callable([
                $content,
                '__toString',
            ])
        ) {
            throw new \InvalidArgumentException(sprintf('variable type error： %s', gettype($content)));
        }

        $this->content = (string)$content;

        return $this;
    }

    /**
     * 发送HTTP状态
     *
     * @param int $code 状态码
     *
     * @return $this
     */
    public function code($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * LastModified.
     *
     * @param string $time
     *
     * @return $this
     */
    public function lastModified($time)
    {
        $this->header['Last-Modified'] = $time;

        return $this;
    }

    /**
     * Expires.
     *
     * @param string $time
     *
     * @return $this
     */
    public function expires($time)
    {
        $this->header['Expires'] = $time;

        return $this;
    }

    /**
     * ETag.
     *
     * @param string $eTag
     *
     * @return $this
     */
    public function eTag($eTag)
    {
        $this->header['ETag'] = $eTag;

        return $this;
    }

    /**
     * 页面缓存控制.
     *
     * @param string $cache 状态码
     *
     * @return $this
     */
    public function cacheControl($cache)
    {
        $this->header['Cache-control'] = $cache;

        return $this;
    }

    /**
     * 页面输出类型.
     *
     * @param string $contentType 输出类型
     * @param string $charset 输出编码
     *
     * @return $this
     */
    public function contentType($contentType, $charset = 'utf-8')
    {
        $contentType = empty($contentType) ? 'text/html' : $contentType;
        $this->header['Content-Type'] = $contentType . '; charset=' . $charset;

        return $this;
    }

    /**
     * 获取头部信息.
     *
     * @param string $name 头部名称
     *
     * @return mixed
     */
    public function getHeader($name = '')
    {
        if (!empty($name)) {
            return isset($this->header[$name]) ? $this->header[$name] : null;
        } else {
            return $this->header;
        }
    }

    /**
     * 获取原始数据.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * 获取输出数据.
     *
     * @return mixed
     */
    public function getContent($type)
    {
        if (null == $this->content) {
            $content = $this->$type($this->data);
            $this->content = (string)$content;
        }

        return $this->content;
    }

    /**
     * 获取状态码
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    public function redirect($url, $statusCode = 302)
    {
        if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            $url = Mll::app()->request->domain() . $url;
        }
        $this->code($statusCode);
        $this->header('Location', $url);

        return $this;
    }
}
