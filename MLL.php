<?php

namespace mll;

/**
 * 版本号
 */
define('MLL_VERSION', '1.0.0');

/**
 * 程序开始时间戳
 */
defined('MLL_BEGIN_TIME') or define('MLL_BEGIN_TIME', microtime(true));

/**
 * 目录分隔符
 */
define('DS', DIRECTORY_SEPARATOR);

/**
 * 框架目录
 */
defined('MLL_PATH') or define('MLL_PATH', __DIR__);

/**
 * 应用目录
 */
defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . DS);

/**
 * 框架应用根目录
 */
defined('ROOT_PATH') or define('ROOT_PATH', dirname(realpath(APP_PATH)) . DS);

/**
 * 调试模式
 */
defined('MLL_DEBUG') or define('MLL_DEBUG', false);

/**
 * 当前环境
 */
defined('MLL_ENV') or define('MLL_ENV', 'prod');

/**
 * 是否为生产环境
 */
defined('MLL_ENV_PROD') or define('MLL_ENV_PROD', MLL_ENV === 'prod');

/**
 * 是否为开发环境
 */
defined('MLL_ENV_DEV') or define('MLL_ENV_DEV', MLL_ENV === 'dev');

/**
 * 是否为测试环境
 */
defined('MLL_ENV_TEST') or define('MLL_ENV_TEST', MLL_ENV === 'test');


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
        spl_autoload_register(['Mll', 'autoload'], true, true);
        //获取配置文件

        //
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