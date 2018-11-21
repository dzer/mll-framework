<?php

namespace Mll\Core;

use Mll\Exception\HttpException;
use Mll\Mll;
use Mll\Response\Response;
use Mll\Controller\IController;

/**
 * 路由类
 *
 * @package Mll\Core
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Route
{
    /**
     * 路由执行，包括pathInfo分析，参数过滤，调用方法，响应结果
     *
     * @return mixed
     * @throws \Exception
     */
    public static function route()
    {
        $request = Mll::app()->request;
        $className = 'app\\' . $request->getModule() . '\\controller\\'
            . $request->getController();
        if (!class_exists($className)) {
            throw new HttpException(404, "class {$className} not found");
        }
        $class = Container::getInstance($className);

        try {
            if (!($class instanceof IController)) {
                throw new HttpException(404, 'ctrl error');
            }
            $cacheKey = '';
            //判断是否走缓存
            if ($request->request('SOURCE_CACHE_TIME') > 0) {
                Mll::app()->cache->init('memcached');
                $cacheKey = $request->getModule() . '\\' . $request->getController()
                    . '\\' . $request->getAction() . '_' . sha1(serialize($request->request()));
                $cacheValue = Mll::app()->cache->get($cacheKey);
                if ($cacheValue !== false) {
                    $isAjax = $request->getIsAjax();
                    $type = $isAjax ? 'json' : 'html';
                    $response = Mll::app()->response->code(200)
                        ->header(['X-Cache-Time' => $request->request('SOURCE_CACHE_TIME')]);
                    return $response->send($cacheValue, $type);
                }
            }

            $view = null;
            $action = $request->getAction();
            $before = $class->beforeAction();
            if ($before === true) {
                if (!method_exists($class, $action)) {
                    throw new HttpException(404, 'method not found');
                }
                $view = $class->$action();
                $class->afterAction();
            } elseif ($before instanceof Response) {
                $view = $before;
            } else {
                throw new \Exception($className . ':' . $action . ' _before() no return true');
            }
            // 输出数据到客户端
            if ($view instanceof Response) {
                $response = $view;
            } elseif (!is_null($view)) {
                // 默认自动识别响应输出类型
                $isAjax = $request->getIsAjax();
                $type = $isAjax ? 'json' : 'html';
                $response = Mll::app()->response->data($view)->type($type);
            } else {
                $response = Mll::app()->response;
            }

            if ($request->request('SOURCE_CACHE_TIME') > 0) {
                Mll::app()->cache->set($cacheKey, $response->getContent(), $request->request('SOURCE_CACHE_TIME'));
            }

            return $response->send();
        } catch (\Exception $e) {
            if ($class instanceof IController) {
                $class->afterAction();
            }
            throw $e;
        }
    }

    /**
     *  路由匹配.
     *
     * @param array $route route配置数组
     * @param string $pathInfo pathInfo
     *
     * @return mixed
     */
    public static function match($route, $pathInfo)
    {
        if (empty($route) || empty($pathInfo)) {
            return false;
        }

        $pathInfo = explode('.', $pathInfo);
        $pathInfo = $pathInfo[0];

        if (isset($route['static'][$pathInfo])) {
            return $route['static'][$pathInfo];
        }

        if (!empty($route['dynamic'])) {
            foreach ($route['dynamic'] as $regex => $rule) {
                if (!preg_match($regex, $pathInfo, $matches)) {
                    continue;
                }
                if (!empty($matches)) {
                    unset($matches[0]);
                    foreach ($matches as $index => $val) {
                        $rule[0] = str_replace("{{$index}}", $val, $rule[0], $count);
                        if (($count) > 0) {
                            unset($matches[$index]);
                        }
                    }
                    if (!empty($rule[1]) && !empty($matches)) {
                        $rule[1] = array_combine($rule[1], $matches);
                    }
                    if (isset($cache)) {
                        $cache->set($pathInfo, json_encode($rule));
                    }

                    return $rule;
                }
            }
        }

        return false;
    }

    /**
     * 分析路由和参数.
     *
     * @param string $url pathInfo
     * @return array 返回 [路由地址, 参数]
     */
    public static function parseUrlPath($url)
    {
        // 分隔符替换 确保路由定义使用统一的分隔符
        //$url = str_replace('|', '/', $url);
        $url = trim($url, '/');
        $path = $var = [];
        if (SERVER_MODEL == 'SwooleHttp') {
            $path = explode('/', $url);
        } else {
            if (false !== strpos($url, '?')) {
                // [模块/控制器/操作?]参数1=值1&参数2=值2...
                $info = parse_url($url);
                $path = explode('/', $info['path']);
                parse_str($info['query'], $var);
            } elseif (strpos($url, '/')) {
                // [模块/控制器/操作]
                $path = explode('/', $url);
            } elseif (false !== strpos($url, '=')) {
                // 参数1=值1&参数2=值2...
                parse_str($url, $var);
            } else {
                $path = [$url];
            }
        }


        if (!empty($url) && '/' !== $url) {
            //路由替换
            $routeMap = self::match(Mll::app()->config->get('route'), $url);
            if (is_array($routeMap)) {
                $path = explode('\\', $routeMap[0]);
                if (!empty($routeMap[1]) && is_array($routeMap[1])) {
                    //参数优先
                    $var += $routeMap[1];
                }
            }
        }

        return [$path, $var];
    }
}
