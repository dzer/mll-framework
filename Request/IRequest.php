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
     * 获取请求参数.
     *
     * @return mixed
     */
    public function getParams();

    /**
     * 设置请求参数.
     */
    public function setParams();

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
     * isAjax
     *
     * @return mixed
     */
    public function isAjax();

    public function getRequestTime($clear = false);
}
