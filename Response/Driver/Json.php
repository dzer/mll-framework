<?php

namespace Mll\Response\Driver;

use Mll\Response\Response;

/**
 * json响应类
 *
 * @package Mll\Response\Driver
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Json extends Response
{
    // 输出参数
    protected $options = [
        'json_encode_param' => JSON_UNESCAPED_UNICODE,
    ];

    /**
     * @var string
     */
    protected $contentType = 'application/json';

    /**
     * 处理数据.
     *
     * @param mixed $data 要处理的数据
     *
     * @return mixed
     *
     * @throws \Exception
     */
    protected function output($data)
    {
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
}
