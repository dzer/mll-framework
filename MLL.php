<?php

namespace mll;

use mll\core\Config;

include __DIR__ . DIRECTORY_SEPARATOR . 'base.php';

class Mll
{
    /**
     * 配置目录
     * @var string
     */
    private static $configPath = 'default';

    /**
     * 系统类库
     * @var array
     */
    private static $libMap = [];

    /**
     * 加载类
     * @var array
     */
    private static $classMap = [];

    public static function run($configPath = null)
    {
        //自动加载
        spl_autoload_register(__CLASS__ . '::autoload', true, true);
        //获取配置文件
        var_dump(ROOT_PATH);
        //分析路由

        //加载配置文件
        Config::load(self::getConfigPath('goods'));

       /* $eh = Config::getField('project', 'exception_handler', __CLASS__ . '::exceptionHandler');
        \set_exception_handler($eh);
        \register_shutdown_function(Config::getField('project', 'fatal_handler', __CLASS__ . '::fatalHandler'));
        if (Config::getField('project', 'error_handler')) {
            \set_error_handler(Config::getField('project', 'error_handler'));
        }
        $timeZone = Config::get('time_zone', 'Asia/Shanghai');
        \date_default_timezone_set($timeZone);*/
        //
    }

    /**
     * 获取配置文件目录路径
     *
     * @param null $module
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
     * 自动加载
     *
     * @param $className
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

        include($classFile);

        if (MLL_DEBUG && !class_exists($className, false) && !interface_exists($className, false) && !trait_exists($className, false)) {
            throw new \Exception("没有找到 '$className'：$classFile");
        }
    }
}