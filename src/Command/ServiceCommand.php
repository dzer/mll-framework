<?php

namespace Mll\Command;

use Mll\Common\ProcessHelper;
use Mll\SwoolHttp;

class ServiceCommand
{
    const OK = 0;
    const UNSPECIFIED_ERROR = 1;
    const EXCEPTION = 2;

    // 是否后台运行
    public $daemon = false;
    // 是否热更新
    public $update = false;
    // PID 文件
    protected $pidFile;


    /**
     * 初始化
     * @param $options
     */
    public function __construct($options)
    {
        // 设置pidfile
        $this->pidFile = '/var/run/mll.pid';
        
        if (in_array('d', $options)) {
            $this->daemon = true;
        } 
        
        if (in_array('u', $options)) {
            $this->update = true;
        }
    }

    /**
     * 启动服务
     *
     * @return int
     */
    public function start()
    {
        
        if ($pid = ProcessHelper::readPidFile($this->pidFile)) {
           echo "mll-service is running, PID : {$pid}" . PHP_EOL;
           return self::OK;
        }
        $server = new SwoolHttp();
        if ($this->update) {
            $server->settings['max_request'] = 1;
        }
        $server->settings['daemonize'] = $this->daemon;
        $server->settings['pid_file']  = $this->pidFile;
        $server->start();
    }

    /**
     * 停止服务
     *
     * @return int
     */
    public function stop()
    {
        if ($pid = ProcessHelper::readPidFile($this->pidFile)) {
            ProcessHelper::kill($pid);
            while (ProcessHelper::isRunning($pid)) {
                // 等待进程退出
                usleep(100000);
            }
            echo 'MLLPHP stop completed.' . PHP_EOL;
        } else {
            echo 'MLLPHP is not running.' . PHP_EOL;
        }
        // 返回退出码
        return self::OK;
    }

    /**
     * 重启服务
     *
     * @return int
     */
    public function restart()
    {
        $this->stop();
        $this->start();
        // 返回退出码
        return self::OK;
    }

    /**
     * 重启工作进程
     *
     * @return int
     */
    public function reload()
    {
        if ($pid = ProcessHelper::readPidFile($this->pidFile)) {
            ProcessHelper::kill($pid, SIGUSR1);
        }
        if (!$pid) {
            Output::writeln('mix-httpd is not running.');
            return self::UNSPECIFIED_ERROR;
        }
        echo 'MLLPHP worker process restart completed.' . PHP_EOL;
        // 返回退出码
        return self::OK;
    }

    /**
     * 查看服务状态
     *
     * @return int
     */
    public function status()
    {
        if ($pid = ProcessHelper::readPidFile($this->pidFile)) {
            echo "MLLPHP is running, PID : {$pid}." . PHP_EOL;
        } else {
            echo "MLLPHP is not running." . PHP_EOL;
        }
        // 返回退出码
        return self::OK;
    }
}