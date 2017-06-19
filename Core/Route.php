<?php

namespace Mll\Core;

use Mll\Controller\IController;
use Mll\Mll;
use Mll\Response\Response;

class Route
{
    public static function route()
    {
        $request = Mll::app()->request;
        $className = 'app\\'.$request->getModule().'\\controller\\'
            .$request->getController();
        if (!class_exists($className)) {
            throw new \Exception('class not found');
        }
        $class = Container::getInstance($className);

        try {
            if (!($class instanceof IController)) {
                throw new \Exception('ctrl error');
            } else {
                $view = null;
                $action = $request->getAction();
                if ($class->beforeAction()) {
                    if (!method_exists($class, $action)) {
                        throw new \Exception('method error');
                    }
                    $view = $class->$action();
                    $class->afterAction();
                } else {
                    throw new \Exception($className.':'.$action.' _before() no return true');
                }
                // 输出数据到客户端
                if ($view instanceof Response) {
                    $response = $view;
                } elseif (!is_null($view)) {
                    // 默认自动识别响应输出类型
                    $isAjax = $request->isAjax();
                    $type = $isAjax ? 'json' : 'html';
                    $response = Response::create($view, $type);
                } else {
                    $response = Response::create();
                }

                return $response->send();
            }
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
     *  @param $route       //config里的route配置数组
     *  @param $pathinfo    //默认取值$_SERVER['PATH_INFO'];
     *
     *  @return mixed
     */
    public static function match($route, $pathinfo)
    {
        if (empty($route) || empty($pathinfo)) {
            return false;
        }

        $pathinfo = explode('.', $pathinfo);
        $pathinfo = $pathinfo[0];

        if (isset($route['static'][$pathinfo])) {
            return $route['static'][$pathinfo];
        }

        foreach ($route['dynamic'] as $regex => $rule) {
            if (!preg_match($regex, $pathinfo, $matches)) {
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
                    $cache->set($pathinfo, json_encode($rule));
                }

                return $rule;
            }
        }

        return false;
    }

    public static function parseUrlPath($url)
    {
        // 分隔符替换 确保路由定义使用统一的分隔符
        $url = str_replace('|', '/', $url);
        $url = trim($url, '/');
        $path = $var = [];
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
