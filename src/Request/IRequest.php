<?php

namespace Mll\Request;

interface IRequest
{
    /**
     * 将不同server的传输数据统一格式.
     *
     * @param string $pathInfo
     * @param mixed $params
     * @return void
     */
    public function parse($pathInfo = null, $params = null);

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
     * 获取请求方式.
     *
     * @return mixed
     */
    public function getMethod();

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
     * @param string|array $name 变量名
     * @param mixed $default 默认值
     * @param string|array $filter 过滤方法
     *
     * @return mixed
     */
    public function route($name = '', $default = null, $filter = '');

    /**
     * 返回应用程序的相对URL。
     *
     * @return string 应用程序的相对URL
     */
    public function getBaseUrl();

    /**
     * 获取request变量.
     *
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string|array $filter 过滤方法
     *
     * @return mixed
     */
    public function request($name = '', $default = null, $filter = '');

    /**
     * 设置获取获取GET参数.
     *
     * @param string|array $name 变量名
     * @param mixed $default 默认值
     * @param string|array $filter 过滤方法
     *
     * @return mixed
     */
    public function get($name = '', $default = null, $filter = '');

    /**
     * 设置获取获取POST参数.
     *
     * @param string $name 变量名
     * @param mixed $default 默认值
     * @param string|array $filter 过滤方法
     *
     * @return mixed
     */
    public function post($name = '', $default = null, $filter = '');

    /**
     * 设置获取当前请求的参数.
     *
     * @param string|array $name 变量名
     * @param mixed $default 默认值
     * @param string|array $filter 过滤方法
     *
     * @return mixed
     */
    public function param($name = '', $default = null, $filter = '');

}
