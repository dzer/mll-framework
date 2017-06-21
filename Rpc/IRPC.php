<?php

namespace Mll\Rpc;

interface IRpc
{
    /**
     * get方法
     *
     * @param string $url 请求链接
     * @param array $data 数据
     * @param array $timeOut 超时时间
     * @return mixed
     */
    public function get($url, $data = array(), $timeOut = null);

    /**
     * post方法
     *
     * @param string $url 请求链接
     * @param array $data 数据
     * @param array $timeOut 超时时间
     * @return mixed
     */
    public function post($url, $data = array(), $timeOut = null);

}
