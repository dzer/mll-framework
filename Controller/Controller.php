<?php

namespace Mll\Controller;

use Mll\Response\Response;

class Controller implements IController
{
    protected static $response;


    protected function json($data = [], $code = 200, $header = [], $options = [])
    {
        return Response::create($data, 'json', $code, $header, $options);
    }
}