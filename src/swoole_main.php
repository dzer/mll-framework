<?php

// MLL 引导文件
// 加载基础文件
require __DIR__ . '/base.php';
require __DIR__ . '/../../../autoload.php';

$options = [];
//判断是不是cli
if (PHP_SAPI != 'cli') {
    throw new \RuntimeException('请在cli模式下运行');
}

//cli 命令参数
foreach ($GLOBALS['argv'] as $key => $value) {
    // 获取命令
    if ($key == 1) {
        $command = $value;
    }
    // 获取选项
    if ($key > 1) {
        if (substr($value, 0, 1) == '-') {
            $options[] = substr($value, 1);
        }
    }
}

$serviceCommand = new Mll\Command\ServiceCommand($options);
$serviceCommand->$command();
