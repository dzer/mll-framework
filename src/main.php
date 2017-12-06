<?php

// MLL 引导文件
// 加载基础文件
require __DIR__ . '/base.php';
require __DIR__ . '/../../../autoload.php';
Mll\Mll::app()->run(SERVER_MODEL);
