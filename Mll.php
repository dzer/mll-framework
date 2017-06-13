<?php

namespace Mll;

use Mll\Config;
use Mll\Exception\Error;
use Mll\Log;
use Mll\Server;
use Mll\Core\Container;

/**
 * Class BaseApp
 *
 * @package Mll
 * @property \Mll\Config\Driver\ArrayFormat $config
 * @property \Mll\Request\IRequest $request
 * @property \Mll\Log\ILog $log
 * @property \Mll\Mll $app
 * @property \Mll\Server\IServer $server
 * @date        2016
 * @copyright   mll
 */
class Mll
{
    public static $debug;

    public static $serveModel;

    public static $app;
    /**
     * 配置目录.
     *
     * @var string
     */
    private static $configPath = 'default';

    /**
     * 系统类库.
     *
     * @var array
     */
    private static $libMap = [];


    /**
     * 加载类.
     *
     * @var array
     */
    private static $classMap = [];

    public function __get($name)
    {
        return Container::get($name);
    }

    /**
     * app
     *
     * @return self
     */
    public static function app()
    {
        return Container::getInstance(__CLASS__);
    }

    public function run($serveModel = 'Http')
    {
        self::$serveModel = $serveModel;
        //自动加载
        //spl_autoload_register(__CLASS__.'::autoload', true, true);

        //服务容器
        Container::addDefinitions([
            'config' => function () {
                return Config\Factory::getInstance();
            },
            'request' => function () {
                return Request\Factory::getInstance();
            },
            'log' => function () {
                return Log\Factory::getInstance();
            },
            'server' => function () {
                return Server\Factory::getInstance(self::$serveModel);
            },
        ]);
        //加载配置文件
        Mll::app()->config->load(self::getConfigPath());

        //时区设置
        date_default_timezone_set(Mll::app()->config->get('time_zone', 'Asia/Shanghai'));

        //设置调试模式
        self::$debug = Mll::app()->config->get('app_debug', true);

        //错误注册
        Error::register();

        //run server
        Mll::app()->server->run();
    }

    /**
     * 获取配置文件目录路径.
     *
     * @param null $module
     *
     * @return array
     */
    public static function getConfigPath($module = null)
    {
        $path = [];
        if (!empty($module)) {
            $moduleConfigPath = ROOT_PATH . DS . 'app' . DS . $module . DS . 'config';
            if (is_dir($moduleConfigPath)) {
                $path[] = $moduleConfigPath;
            }
        }
        $path[] = ROOT_PATH . DS . 'app' . DS . 'config';

        return $path;
    }

    /**
     * 自动加载.
     *
     * @param $className
     *
     * @throws \Exception
     */
    public static function autoload($className)
    {
        if (isset(static::$classMap[$className])) {
            $classFile = static::$classMap[$className];
        } elseif (strpos($className, '\\') !== false) {
            $classFile = str_replace('\\', DS, $className) . '.php';
            if ($classFile === false || !is_file($classFile)) {
                return;
            }
        } else {
            return;
        }

        include $classFile;

        if (self::$debug && !class_exists($className, false) && !interface_exists($className, false)
            && !trait_exists($className, false)
        ) {
            throw new \Exception("没有找到 '$className'：$classFile");
        }
    }
}
