<?php

namespace Mll;

use Mll\Config;
use Mll\Core\Container;

/**
 * Class BaseApp
 *
 * @package Mll
 * @property \Mll\Config\Driver\ArrayFormat $config
 * @property \Mll\Request\IRequest $request
 * @property \Mll\Mll $app
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
        //错误注册
        if (MLL_DEBUG && class_exists("\\Whoops\\Run")) {
            $whoops = new \Whoops\Run();
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
            $whoops->register();
        } else {

        }

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
        ]);


        Mll::app()->config->load(self::getConfigPath('goods'));

        self::$debug = Mll::app()->config->get('app_debug', 1);
        //Mll::app()->request::parse('dd');
        var_dump(Mll::app()->config->all());
        die;
        //纯静态框架

        //自定义容器 符合psr-11
        //获取配置 在build get时缓存(暂定)
        //@property \yii\web\Request|\yii\console\Request $request The request component. This property is read-only.
        self::$container = new Container();

        self::$config = Config\Factory::getInstance();
        self::$request = Request\Factory::getInstance(self::$serveModel);

        //分析路由

        //加载配置文件
        self::$config = self::$config->load(self::getConfigPath('goods'));

        var_dump(self::$config);
        die;
        var_dump(Mll::$config->cache->mll->host);

        var_dump(Config::all());
        $timeZone = Mll::$container->get('config')->get('time_zone', 'Asia/Shanghai');
        date_default_timezone_set($timeZone);

        /* $eh = Config::getField('project', 'exception_handler', __CLASS__ . '::exceptionHandler');
         \set_exception_handler($eh);
         \register_shutdown_function(Config::getField('project', 'fatal_handler', __CLASS__ . '::fatalHandler'));
         if (Config::getField('project', 'error_handler')) {
             \set_error_handler(Config::getField('project', 'error_handler'));
         }
         $timeZone = Config::get('time_zone', 'Asia/Shanghai');
         \date_default_timezone_set($timeZone);*/
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

        if (MLL_DEBUG && !class_exists($className, false) && !interface_exists($className, false)
            && !trait_exists($className, false)
        ) {
            throw new \Exception("没有找到 '$className'：$classFile");
        }
    }
}
