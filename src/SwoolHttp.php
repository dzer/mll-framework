<?php

namespace Mll;

use Mll\Common\ProcessHelper;
use Mll\Config;
use Mll\Exception\Error;
use Mll\Core\Container;
use Mll\Session\Session;

/**
 * Http服务22222
 *
 * @package Mll\Server\Driver
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class SwoolHttp
{

    // 虚拟主机
    public $virtualHost = [
        // 主机
        'host' => '192.168.0.106',
        // 端口
        'port' => 9501,
    ];

    // 运行参数
    public $settings = [];

    // 默认运行参数
    protected $_settings = [
        // 开启协程
        'enable_coroutine' => false,
        // 连接处理线程数
        'reactor_num' => 8,
        // 工作进程数
        'worker_num' => 8,
        // 进程的最大任务数
        'max_request' => 10000000,
        // 异步安全重启
        'reload_async' => true,
        // 退出等待时间
        'max_wait_time' => 60,
        // PID 文件
        'pid_file' => '/var/run/mll.pid',
        // 日志文件路径
        'log_file' => '/tmp/mll.log',
        // 开启后，PDO 协程多次 prepare 才不会有 40ms 延迟
        'open_tcp_nodelay' => true
    ];

    // 服务器
    protected $_server;

    // 主机
    protected $_host;

    // 端口
    protected $_port;

    // 初始化
    protected function initialize()
    {
        // 初始化参数
        $this->_host = $this->virtualHost['host'];
        $this->_port = $this->virtualHost['port'];
        $this->settings += $this->_settings;
        // 实例化服务器
        $this->_server = new \Swoole\Http\Server($this->_host, $this->_port);
    }

    // 启动服务
    public function start()
    {
        $this->initialize();
        $this->welcome();
        $this->onStart();
        $this->onManagerStart();
        $this->onWorkerStart();
        $this->onRequest();
        $this->_server->set($this->settings);
        $this->_server->start();
    }

    // 主进程启动事件
    protected function onStart()
    {
        $this->_server->on('Start', function ($server) {
            // 进程命名
            ProcessHelper::setTitle("mll: master {$this->_host}:{$this->_port}");
        });
    }

    // 管理进程启动事件
    protected function onManagerStart()
    {
        $this->_server->on('ManagerStart', function ($server) {
            // 进程命名
            ProcessHelper::setTitle("mll: manager");
        });
    }

    // 工作进程启动事件
    protected function onWorkerStart()
    {
        $this->_server->on('WorkerStart', function ($server, $workerId) {
            // 进程命名
            if ($workerId < $server->setting['worker_num']) {
                ProcessHelper::setTitle("mll: worker #{$workerId}");
            } else {
                ProcessHelper::setTitle("mll: task #{$workerId}");
            }
            Mll::app()->run(SERVER_MODEL);
        });
    }

    // 请求事件
    protected function onRequest()
    {
        $this->_server->on('request', function ($request, $response) {
            $_GET = $request->get;
            $_POST = $request->post;
            $_COOKIE = $request->cookie;
            $_FILES = $request->files;
            $_SERVER = array_change_key_case($request->server, CASE_UPPER);
            /*var_dump($_GET);
            $response->end(print_r($_GET, true));
            return;*/
            //var_dump($_SERVER);return;
            //$response->header(Mll::app()->request->header());
            //$response->header(Mll::app()->respon->header());
            try {
               $rs = Mll::app()->server->run();
            } catch (\Throwable $e) {
                $rs = Error::appException($e);
            }
            $response->end($rs);
            // 执行请求
            //
        });
    }

    // 欢迎信息
    protected function welcome()
    {
        $swooleVersion = swoole_version();
        $phpVersion = PHP_VERSION;
        echo <<<EOL
                           _____
_______ ___ _____ ___ _____  / /_  ____
__/ __ `__ \/ /\ \/ / / __ \/ __ \/ __ \
_/ / / / / / / /\ \/ / /_/ / / / / /_/ /
/_/ /_/ /_/_/ /_/\_\/ .___/_/ /_/ .___/
                   /_/         /_/


EOL;
        echo 'Server      Name:      mll' . PHP_EOL;
        echo 'Framework   Version:   ' . MLL_VERSION . PHP_EOL;
        echo "PHP         Version:   {$phpVersion}" . PHP_EOL;
        echo "Swoole      Version:   {$swooleVersion}" . PHP_EOL;
        echo "Listen      Addr:      {$this->_host}" . PHP_EOL;
        echo "Listen      Port:      {$this->_port}" . PHP_EOL;
        echo 'Hot         Update:    ' . ($this->settings['max_request'] == 1 ? 'enabled' : 'disabled') . PHP_EOL;
        echo 'Coroutine   Mode:      ' . ($this->settings['enable_coroutine'] ? 'enabled' : 'disabled') . PHP_EOL;
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
        } else {
            $path[] = ROOT_PATH . DS . 'app' . DS . 'common' . DS . 'config';
        }

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
            $classFile = MLL_PATH . static::$classMap[$className];
        } elseif (strpos($className, '\\') !== false) {
            $classFile = ROOT_PATH . DS . str_replace('\\', DS, $className) . '.php';
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

    /**
     * Xhprof 性能分析
     */
    public static function xhprof()
    {
        if (Mll::app()->config->get('xhprof.enable', false)
            && function_exists('xhprof_enable')
        ) {
            xhprof_enable(XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY | XHPROF_FLAGS_NO_BUILTINS);
        }
    }
}
