<?php

namespace Mll\Log\Driver;

use Mll\Log\Base;
use Mll\Log\ILog;

/**
 * 本地化调试输出到文件.
 */
class File extends Base implements ILog
{
    private $config = array(
        'time_format' => 'c', //ISO 8601 格式的日期
        'file_size' => 2097152,
        'path' => '/runtime/log',
        'level' => 'all', //默认所有，或者逗号隔开warning,error
        'separator' => ' | ',
        'suffix' => '.log',
    );

    private $logs;

    // 实例化并传入参数
    public function __construct($config = [])
    {
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }
    }

    /**
     * 任意等级的日志记录.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return bool
     */
    public function log($level, $message, array $context = array())
    {
        if (empty($level) || empty($message)) {
            return false;
        }
        $now = date($this->config['time_format']);
        $separator = $this->config['separator'];
        $logStr = $now . $separator . $level . $separator . $message;
        if (!empty($context)) {
            $logStr .= $separator . implode($separator, array_map(function ($value) {
                    return json_encode($value, JSON_UNESCAPED_UNICODE);
            }, $context));
        }
        $this->logs[$level][] = $logStr;

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
        $destination = ROOT_PATH . $this->config['path'] . DS . date('Ym') . DS . date('d') . $this->config['suffix'];

        $path = dirname($destination);
        !is_dir($path) && mkdir($path, 0755, true);

        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        if (is_file($destination) && floor($this->config['file_size']) <= filesize($destination)) {
            rename($destination, dirname($destination) . DS . $_SERVER['REQUEST_TIME'] . '-' . basename($destination));
        }
        $allowLevel = explode(',', $this->config['level']);
        foreach ($this->logs as $level => $val) {
            if (in_array('all', $allowLevel) || in_array($val['level'], $allowLevel)) {
                // 独立记录的日志级别
                $filename = $path . DS . date('d') . '_' . $level . '.log';
                error_log(implode("\r\n", $val) . "\r\n", 3, $filename);
            }
        }
        $this->logs = null;
        return true;
    }
}