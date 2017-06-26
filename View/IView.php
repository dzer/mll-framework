<?php

namespace Mll\View;

interface IView
{

    /**
     * 渲染数据
     *
     * @return mixed
     */
    public function display($template, $params = []);
}
