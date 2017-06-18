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
        $className = 'app\\' . $request->getModule() . '\\controller\\'
            . $request->getController();
        if (!class_exists($className)) {
            throw new \Exception("class not found");
        }
        $class = Container::getInstance($className);

        try {
            if (!($class instanceof IController)) {
                throw new \Exception("ctrl error");
            } else {
                $view = null;
                $action = $request->getAction();
                if ($class->beforeAction()) {
                    if (!method_exists($class, $action)) {
                        throw new \Exception("method error");
                    }
                    $view = $class->$action();
                    $class->afterAction();
                } else {
                    throw new \Exception($className . ':' . $action . ' _before() no return true');
                }
                // 输出数据到客户端
                if ($view instanceof Response) {
                    $response = $view;
                } elseif (!is_null($view)) {
                    // 默认自动识别响应输出类型
                    $isAjax   = $request->isAjax();
                    $type     = $isAjax ? 'json' : 'html';
                    $response = Response::create($view, $type);
                } else {
                    $response = Response::create();
                }

                return $response->send();
            }
        } catch (\Exception $e) {
            /*if (Request::isLongServer()) {
                $result = \call_user_func(Config::getField('project', 'exception_handler', 'ZPHP\ZPHP::exceptionHandler'), $e);
                if ($class instanceof IController) {
                    $class->_after();
                }
                return $result;
            }*/
            if ($class instanceof IController) {
                $class->afterAction();
            }
            throw $e;
        }
    }

    /**
     *  路由匹配
     *  param $route       //config里的route配置数组
     *  param $pathinfo    //默认取值$_SERVER['PATH_INFO'];
     *  return array("ctrl class", "method", array params);
     *  examples:
     *  config/route.php
     *  return array(
     *      'static'=>array(
     *           'reg'=>array(
     *              'main\\main',
     *              'reg'，
     *              array("callurl"=>'http://zphp.com'),    //默认参数，可选项
     *           ),
     *      )
     *      'dynamic'=>array(
     *           '/^\/product\/(\d+)$/iU''=>array(                                  //匹配 /product/123 将被匹配
     *              'main\\product',            //ctrl class
     *              'show',                     //ctrl method
     *              array('id'),                //匹配参数                          //名为id的参数将被赋值 123
     *              '/product/{id}'             //格式化
     *           ),
     *      )
     *
     *
     *  )
     *
     *  http://host/reg 将会匹配到 static 中 reg 的定义规则，将执行apps/ctrl/main/main.php中的reg方法，并有默认参数callurl值为http://zphp.com
     *  http://host/product/123 将会匹配到 dynamic 中 /^\/product\/(\d+)$/iU 的定义规则，
     *  将执行 apps/ctrl/main/product.php中的show方法，并把123解析为参数id的值
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

        /*if (!empty($route['cache'])) {
            $config = ZConfig::getField('cache', 'locale', array());
            if (!empty($config)) {
                $cache = ZCache::getInstance($config['adapter'], $config);
                $result = $cache->get($pathinfo);
                if (!empty($result)) {
                    return json_decode($result, true);
                }
            }
        }*/


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

    /**
     *  返回友好的url
     *  param $ctrl         //ctrl class
     *  param $method       //所要执行的method
     *  param $params       //额外参数
     *  return
     *  如果是静态路由，直接返回 路由的key值
     *  如果是动态路由，会根据匹配到配置的友好url进行格式化处理
     *  examples:
     *  config/route.php
     *  return array(
     *      'static'=>array(
     *           'reg'=>array(
     *              'main\\main', 'reg'
     *           ),
     *      )
     *      'dynamic'=>array(
     *           '/^\/product\/(\d+)$/iU''=>array(                                  //匹配 /product/123 将被匹配
     *              'main\\product',            //ctrl class
     *              'show',                     //ctrl method
     *              array('id'),                //匹配参数                          //名为id的参数将被赋值 123
     *              '/product/{id}'             //格式化
     *           ),
     *      )
     *
     *
     *  )
     *  如果配置了route:
     *  调用 \ZPHP\Common\Route::makeUrl('main\\main', 'reg'),  将生成url http://host/reg
     *  调用 \ZPHP\Common\Route::makeUrl('main\\product', 'show', array("id"=>123, "uid"=>321)),  将生成url http://host/product/123?uid=321
     */
    public static function makeUrl($ctrl, $method, $params = array())
    {
        $appUrl = ZConfig::getField('project', 'app_host', "");
        $ctrlName = ZConfig::getField('project', 'ctrl_name', 'a');
        $methodName = ZConfig::getField('project', 'method_name', 'm');
        if (empty($appUrl)) {
            $appUrl = $_SERVER['HTTP_HOST'];
        }
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
            $appUrl = 'https://' . $appUrl;
        } else {
            $appUrl = 'http://' . $appUrl;
        }
        $routes = ZConfig::get('route', false);
        if (!empty($routes)) {
            if (isset($routes['cache'])) {
                if (!empty($route['cache'])) {
                    $config = ZConfig::getField('cache', 'locale', array());
                    if (!empty($config)) {
                        $cache = ZCache::getInstance($config['adapter'], $config);
                        $cacheKey = self::getKey($ctrl, $method, $params);
                        $result = $cache->get($cacheKey);
                        if (!empty($result)) {
                            return $result;
                        }
                    }
                }
                unset($routes['cache']);
            }
            $ext = '';
            if (!empty($routes['ext'])) {
                $ext = $routes['ext'];
                unset($routes['ext']);
            }
            $result = false;
            foreach ($routes as $type => $rules) {
                foreach ($rules as $path => $rule) {
                    if ($rule[0][0] == '{' || $rule[0] == str_replace('/', '\\', $ctrl)) {
                        if ($rule[1][0] != '{' && $rule[1] != $method) {
                            continue;
                        }
                        if ('static' == $type) {
                            if (empty($params)) {
                                if ('' == $path || '/' == $path) {
                                    $result = $appUrl . $path;
                                } else {
                                    $result = $appUrl . $path . $ext;
                                }

                            } else {
                                $result = $appUrl . $path . $ext . '?' . http_build_query($params);
                            }
                        } else {
                            $realPath = $rule[3];
                            $realPath = str_replace(array('{c}', '{m}'), array($ctrl, $method), $realPath);
                            if (!empty($rule[2])) {
                                foreach ($rule[2] as $key) {
                                    if (isset($params[$key])) {
                                        $realPath = str_replace("{{$key}}", $params[$key], $realPath);
                                        unset($params[$key]);
                                    }
                                }
                            }
                            if (empty($params)) {
                                $result = $appUrl . $realPath . $ext;
                            } else {
                                $result = $appUrl . $realPath . $ext . '?' . http_build_query($params);
                            }
                        }
                        if ($result) {
                            if (isset($cacheKey)) {
                                $cache->set($cacheKey, $result);
                            }
                            return $result;
                        }
                    }
                }
            }
        }
        if (empty($params)) {
            return $appUrl . "?{$ctrlName}={$ctrl}&{$methodName}={$method}";
        }
        return $appUrl . "?{$ctrlName}={$ctrl}&{$methodName}={$method}&" . http_build_query($params);
    }

    private static function getKey()
    {
        return ZConfig::getField('project', 'project_name') . "_route_" . json_encode(func_get_args());
    }
}