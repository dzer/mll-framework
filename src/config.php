<?php

namespace Mll;

use Mll\Core\Container;

return [
    'container' => [
        'log' => function () {
            return Log\Factory::getInstance(
                Mll::app()->config->get('log.driver', 'file')
            );
        },
        'server' => function () {
            return Server\Factory::getInstance(SERVER_MODEL);
        },
        'request' => function () {
            return Request\Factory::getInstance(SERVER_MODEL);
        },
        'rpc' => function () {
            return Rpc\Factory::getInstance(
                Mll::app()->config->get('rpc.driver', 'yar')
            );
        },
        'view' => function () {
            return View\Factory::getInstance(
                Mll::app()->config->get('view.driver', 'php')
            );
        },
        'write' => function () {
            return Write\Factory::getInstance();
        },
        'curl' => function () {
            return Container::getInstance(__NAMESPACE__.'\\Curl\\Curl');
        },
    ],
    'classMap' => [
        'Mll\\Core\\Container' => '/Core/Container.php',
        'Mll\\Config\\Factory' => '/Config/Factory.php',
        'Mll\\Config\\Driver\\ArrayFormat' => '/Config/Driver/ArrayFormat.php',
        'Mll\\Config\\IConfig' => '/Config/IConfig.php',
        'Mll\\Common\\Dir' => '/Common/Dir.php',
        'Mll\\Exception\\Error' => '/Exception/Error.php',
        'Mll\\Server\\Factory' => '/Server/Factory.php',
        'Mll\\Server\\Driver\\Http' => '/Server/Driver/Http.php',
        'Mll\\Server\\IServer' => '/Server/IServer.php',
        'Mll\\Request\\Factory' => '/Request/Factory.php',
        'Mll\\Request\\Driver\\Http' => '/Request/Driver/Http.php',
        'Mll\\Request\\Base' => '/Request/Base.php',
        'Mll\\Request\\IRequest' => '/Request/IRequest.php',
        'Mll\\Core\\Route' => '/Core/Route.php',
        'Mll\\Controller' => '/Controller.php',
        'Mll\\Controller\\IController' => '/Controller/IController.php',
        'Mll\\Response\\Response' => '/Response/Response.php',
        'Mll\\Response\\Driver\\Json' => '/Response/Driver/Json.php',
        'Mll\\Common\\Common' => '/Common/Common.php',
        'Mll\\Log\\Driver\\File' => '/Log/Driver/File.php',
        'Mll\\Log\\Driver\\Cache' => '/Log/Driver/Cache.php',
        'Mll\\Log\\Base' => '/Log/Base.php',
        'Mll\\Log\\ILog' => '/Log/ILog.php',
        'Mll\\Log\\Factory' => '/Log/Factory.php',
    ],
];