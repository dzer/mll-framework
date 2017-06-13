<?php

namespace Mll\Common;
use Mll\Mll;

class Debug
{
    private static $xhprof = false;
    private static $records;

    private static $DEBUG_TRACE = false;

    public static function getMicroTime()
    {
        list($usec, $sec) = \explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    public static function start($key = 'ALL')
    {
        if (!self::$xhprof && Mll::app()->log->get('project.xhprof', true) && \function_exists('xhprof_enable')) {
            /*require(ZPHP::getLibPath() . DS . 'xhprof_lib' . DS . 'utils' . DS . 'xhprof_lib.php');
            require(ZPHP::getLibPath() . DS . 'xhprof_lib' . DS . 'utils' . DS . 'xhprof_runs.php');
            \xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);*/
            self::$xhprof = true;
        }
        self::$records[$key]['start_time'] = self::getMicroTime();
        self::$records[$key]['memory_use'] = memory_get_usage();
    }

    public static function end($key = 'ALL', $logName = 'debug')
    {
        $endTime = self::getMicroTime();
        $run_id = 0;
        if (self::$xhprof) {
            /*$xhprof_data = \xhprof_disable();
            $xhprof_runs = new \XHProfRuns_Default();
            $run_id = $xhprof_runs->save_run($xhprof_data, 'random');*/
        }
        $times = $endTime - self::$records[$key]['start_time'];
        $mem_use = memory_get_usage() - self::$records[$key]['memory_use'];
        Mll::app()->log->info($logName, array(
            $times,
            self::convert($mem_use),
            $run_id, $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
            $_SERVER['REQUEST_URI'])
        );
    }
    private static function convert($size)
    {
        $unit = array('B', 'K', 'M', 'G', 'T', 'P');
        return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }

}

