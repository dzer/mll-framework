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
}