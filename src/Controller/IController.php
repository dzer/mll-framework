<?php

namespace Mll\Controller;

interface IController
{
    /**
     * 响应json格式.
     *
     * @param array $data 数据
     * @param int $code http状态码
     * @param array $header 响应头
     * @param array $options 其他参数
     *
     * @return mixed
     */
    public function json($data = [], $code = 200, $header = [], $options = []);

    /**
     * 前置方法
     *
     * @return mixed
     */
    public function beforeAction();

    /**
     * 后置方法
     *
     * @return mixed
     */
    public function afterAction();
}
