<?php

namespace Mll;

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
    ],
];