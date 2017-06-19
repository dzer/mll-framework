<?php

namespace Mll\Request;

interface IRequest
{
    /**
     * 将不同server的传输数据统一格式.
     *
     * @param $request
     *
     * @return mixed
     */
    public function parse($request = null);

    /**
     * 获取模块.
     *
     * @return mixed
     */
    public function getModule();

    /**
     * 获取控制器.
     *
     * @return mixed
     */
    public function getController();

    /**
     * 获取方法.
     *
     * @return mixed
     */
    public function getAction();

    /**
     * 获取方法.
     *
     * @return mixed
     */
    public function getMethod();

    /**
     * isAjax.
     *
     * @return mixed
     */
    public function isAjax();

    /**
     * 获取请求时间.
     *
     * @param bool $clear
     *
     * @return mixed
     */
    public function getRequestTime($clear = false);

    /**
     * 设置获取获取路由参数.
     *
     * @param string|array $name    变量名
     * @param mixed        $default 默认值
     * @param string|array $filter  过滤方法
     *
     * @return mixed
     */
    public function route($name = '', $default = null, $filter = '');
}
