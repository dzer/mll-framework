<?php

namespace Mll\Server\Driver;

use Mll\Server\IServer;
use Mll\Core;

class Http implements IServer
{
    public function run()
    {
        return Core\Route::route();
    }
}
