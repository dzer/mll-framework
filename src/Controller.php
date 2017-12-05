<?php

namespace Mll;

use Mll\Controller\IController;
use Mll\Response\Response;

class Controller implements IController
{
    protected static $response;


    public function beforeAction()
    {
        return true;
        // TODO: Implement beforeAction() method.
    }

    public function afterAction()
    {
        // TODO: Implement afterAction() method.
    }

    public function json($data = [], $code = 200, $header = [], $options = [])
    {
        return Response::create($data, 'json', $code, $header, $options);
    }

    public function render($template, $params = [], $code = 200, $header = [], $options = [])
    {
        $content = Mll::app()->view->fetch($template, $params);
        return Response::create($content, 'view', $code, $header, $options);
    }

    public function assign($name, $value = '')
    {
        return Mll::app()->view->assign($name, $value);
    }

    public function redirect($url)
    {
        return Response::create()->redirect($url);
    }
}