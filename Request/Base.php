<?php

namespace Mll\Request\Driver;

use Mll\Request\IRequest;

abstract class Base implements IRequest
{
    protected static $server;

    protected static $params;

    protected static $module;

    protected static $controller;

    protected static $method;

    /**
     * 获取当前请求URL的pathinfo信息(不含URL后缀).
     *
     * @return string
     */
    public function path()
    {
        /*if (is_null($this->path)) {
            $suffix   = Config::getField('url_html_suffix');
            $pathinfo = $this->pathinfo();
            if (false === $suffix) {
                // 禁止伪静态访问
                $this->path = $pathinfo;
            } elseif ($suffix) {
                // 去除正常的URL后缀
                $this->path = preg_replace('/\.(' . ltrim($suffix, '.') . ')$/i', '', $pathinfo);
            } else {
                // 允许任何后缀访问
                $this->path = preg_replace('/\.' . $this->ext() . '$/i', '', $pathinfo);
            }
        }
        return $this->path;*/
    }

    /**
     * 获取当前请求URL的pathinfo信息（含URL后缀）.
     *
     * @return string
     */
    public function pathInfo()
    {
        /*if (is_null($this->pathinfo)) {
            if (isset($_GET[Config::get('var_pathinfo')])) {
                // 判断URL里面是否有兼容模式参数
                $_SERVER['PATH_INFO'] = $_GET[Config::get('var_pathinfo')];
                unset($_GET[Config::get('var_pathinfo')]);
            } elseif (IS_CLI) {
                // CLI模式下 index.php module/controller/action/params/...
                $_SERVER['PATH_INFO'] = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '';
            }

            // 分析PATHINFO信息
            if (!isset($_SERVER['PATH_INFO'])) {
                foreach (Config::get('pathinfo_fetch') as $type) {
                    if (!empty($_SERVER[$type])) {
                        $_SERVER['PATH_INFO'] = (0 === strpos($_SERVER[$type], $_SERVER['SCRIPT_NAME'])) ?
                            substr($_SERVER[$type], strlen($_SERVER['SCRIPT_NAME'])) : $_SERVER[$type];
                        break;
                    }
                }
            }
            $this->pathinfo = empty($_SERVER['PATH_INFO']) ? '/' : ltrim($_SERVER['PATH_INFO'], '/');
        }
        return $this->pathinfo;*/
    }

    /**
     * 将不同server的传输数据统一格式.
     *
     * @param $requestParams
     */
    abstract public static function parse($requestParams);

    /**
     * 获取请求参数.
     *
     * @return mixed
     */
    public static function getParams()
    {
    }

    /**
     * 设置请求参数.
     */
    public static function setParams()
    {
    }

    /**
     * 获取模块.
     *
     * @return mixed
     */
    public static function getModule()
    {
    }

    /**
     * 获取控制器.
     *
     * @return mixed
     */
    public static function getController()
    {
    }

    /**
     * 获取方法.
     *
     * @return mixed
     */
    public static function getMethod()
    {
    }
}
