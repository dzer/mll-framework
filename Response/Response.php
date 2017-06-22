<?php

namespace Mll\Response;

use Mll\Common\Common;

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
    protected $contentType = 'text/html';

    // 字符集
    protected $charset = 'utf-8';

    //状态
    protected $code = 200;

    // 输出参数
    protected $options = [];
    // header参数
    protected $header = [];

    protected $content = null;

    const RESPONSE_TIME_KEY = 'X-Run-Time';

    /**
     * 架构函数.
     *
     * @param mixed $data    输出数据
     * @param int   $code    http状态码
     * @param array $header  响应头
     * @param array $options 输出参数
     */
    public function __construct($data = '', $code = 200, array $header = [], $options = [])
    {
        $this->data($data);
        $this->header = $header;
        $this->code = $code;
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        $this->header[self::RESPONSE_TIME_KEY] = Common::getMicroTime() - Common::getMicroTime(MLL_BEGIN_TIME);

        $this->contentType($this->contentType, $this->charset);
    }

    /**
     * 创建Response对象
     *
     * @param mixed  $data    输出数据
     * @param string $type    输出类型
     * @param int    $code    http状态码
     * @param array  $header
     * @param array  $options 输出参数
     *
     * @return Response
     */
    public static function create($data = '', $type = '', $code = 200, array $header = [], $options = [])
    {
        $type = empty($type) ? 'null' : strtolower($type);

        $class = false !== strpos($type, '\\') ? $type : '\\Mll\\response\\Driver\\'.ucfirst($type);
        if (class_exists($class)) {
            $response = new $class($data, $code, $header, $options);
        } else {
            $response = new static($data, $code, $header, $options);
        }

        return $response;
    }

    /**
     * 发送数据到客户端.
     *
     * @return mixed 响应数据
     *
     * @throws \InvalidArgumentException
     */
    public function send()
    {
        // 处理输出数据
        $data = $this->getContent();

        if (!headers_sent() && !empty($this->header)) {
            // 发送状态码
            http_response_code($this->code);
            // 发送头部信息
            foreach ($this->header as $name => $val) {
                header($name.':'.$val);
            }
        }

        echo $data;

        if (strtolower(SERVER_MODEL) == 'rpc') {
            return ob_get_clean();
        }

        if (strtolower(SERVER_MODEL) == 'http' && function_exists('fastcgi_finish_request')) {
            // 提高页面响应
            fastcgi_finish_request();
        }
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
     * @param string|array $name  参数名
     * @param string       $value 参数值
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

        $this->content = (string) $content;

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
     * @param string $charset     输出编码
     *
     * @return $this
     */
    public function contentType($contentType, $charset = 'utf-8')
    {
        $this->header['Content-Type'] = $contentType.'; charset='.$charset;

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
    public function getContent()
    {
        if (null == $this->content) {
            $content = $this->output($this->data);

            if (null !== $content && !is_string($content) && !is_numeric($content) && !is_callable([
                    $content,
                    '__toString',
                ])
            ) {
                throw new \InvalidArgumentException(sprintf('variable type error： %s', gettype($content)));
            }

            $this->content = (string) $content;
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
}
