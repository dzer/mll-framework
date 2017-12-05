<?php

namespace Mll\Response\Driver;

use Mll\Response\Response;
use Mll\View\V as ViewTemplate;

class View extends Response
{
    // 输出参数
    protected $options     = [];

    protected $contentType = 'text/html';

    /**
     * 处理数据
     * @access protected
     * @param mixed $data 要处理的数据
     * @return mixed
     */
    protected function output($data)
    {
        // 渲染模板输出
        return $data;
    }


}
