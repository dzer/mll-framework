<?php

namespace Mll\Core;

use Mll\Common\Dir;

class Config
{
    /**
     * load.
     *
     * @param $configPathArr
     *
     * @return array
     */
    public static function load($configPathArr)
    {
        $config = array();
        if (is_array($configPathArr)) {
            foreach ($configPathArr as $configPath) {
                $files = Dir::tree($configPath, '/.php$/');
                array_map(function ($file) use (&$config) {
                    $config += include "{$file}";
                }, $files);
            }
        }

        return $config;
    }
}
