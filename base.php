<?php
/**
 * 版本号.
 */
define('MLL_VERSION', '1.0.0');

/*
 * 目录分隔符
 */
define('DS', DIRECTORY_SEPARATOR);

/*
 * 程序开始时间戳
 */
defined('SERVER_MODEL') or define('SERVER_MODEL', 'Http');

/*
 * 程序开始时间戳
 */
defined('MLL_BEGIN_TIME') or define('MLL_BEGIN_TIME', microtime());

/*
 * 程序开始内存分配量
 */
defined('MLL_BEGIN_MEMORY') or define('MLL_BEGIN_MEMORY', memory_get_usage());

/*
 * 框架目录
 */
defined('MLL_PATH') or define('MLL_PATH', __DIR__);

/*
 * 应用目录
 */
defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']));

/*
 * 框架应用根目录
 */
defined('ROOT_PATH') or define('ROOT_PATH', dirname(realpath(APP_PATH)));

/*
 * 调试模式
 */
defined('MLL_DEBUG') or define('MLL_DEBUG', false);

/*
 * 当前环境
 */
defined('MLL_ENV') or define('MLL_ENV', 'dev');

/*
 * 是否为生产环境
 */
defined('MLL_ENV_PROD') or define('MLL_ENV_PROD', MLL_ENV === 'prod');

/*
 * 是否为开发环境
 */
defined('MLL_ENV_DEV') or define('MLL_ENV_DEV', MLL_ENV === 'dev');

/*
 * 是否为测试环境
 */
defined('MLL_ENV_TEST') or define('MLL_ENV_TEST', MLL_ENV === 'test');

/*
 * 规则日志类型
 */
defined('LOG_TYPE_RULE') or define('LOG_TYPE_RULE', 'RULE');

/*
 * CURL日志类型
 */
defined('LOG_TYPE_CURL') or define('LOG_TYPE_CURL', 'CURL');

/*
 * RPC日志类型
 */
defined('LOG_TYPE_RPC') or define('LOG_TYPE_RPC', 'RPC');

/*
 * 普通日志类型
 */
defined('LOG_TYPE_GENERAL') or define('LOG_TYPE_GENERAL', 'GENERAL');