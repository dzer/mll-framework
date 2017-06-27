<?php

namespace Mll\Log\Driver;

use Mll\Common\MemcacheQueue;
use Mll\Log\Base;
use Mll\Log\ILog;
use Mll\Mll;

/**
 * 保存到缓存.
 *
 * @package Mll\Log\Driver
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Cache extends Base implements ILog
{
    private $config = array(
        'time_format' => 'c', //ISO 8601 格式的日期
        'cache_server' => 'code',
        'expire' => 600,
        'level' => 'all', //默认所有，或者逗号隔开warning,error
        'queue_name' => 'mll_log_queue'
    );

    /**
     * 日志
     * @var array
     */
    private $logs;

    /**
     * 实例化并传入参数.
     * @param array $config 配置文件
     */
    public function __construct($config = [])
    {
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }
    }

    /**
     * 任意等级的日志记录.
     *
     * @param mixed $level 日志级别
     * @param string $message 消息
     * @param array $context 内容
     * @param string $type 类型
     *
     * @return bool
     */
    public function log($level, $message, array $context = array(), $type = LOG_TYPE_GENERAL)
    {
        if (empty($level) || empty($message)) {
            return false;
        }

        $now = date($this->config['time_format']);
        if (!isset($context['traceId'])) {
            $context['traceId'] = Mll::app()->request->getTraceId();
        }
        $this->logs[$level][] = array(
            'time' => $now,
            'microtime' => microtime(true),
            'server' => $_SERVER['REMOTE_ADDR'],
            'level' => $level,
            'type' => $type,
            'requestId' => Mll::app()->request->getRequestId(),
            'message' => $message,
            'content' => $context
        );
        return true;
    }

    /**
     * 日志写入接口.
     *
     * @return bool
     */
    public function save()
    {
        if (empty($this->logs)) {
            return true;
        }
        $log = [];
        $allowLevel = explode(',', $this->config['level']);
        foreach ($this->logs as $level => $val) {
            if (in_array('all', $allowLevel) || in_array($val['level'], $allowLevel)) {
                // 独立记录的日志级别
                $log = array_merge($log, $val);
            }
        }
        $queue = new MemcacheQueue($this->config['cache_server'], $this->config['queue_name'], $this->config['expire']);
        $queue->add(json_encode($log));
        $this->logs = null;
        return true;
    }
}
