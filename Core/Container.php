<?php

namespace Mll\Core;

/**
 * 服务容器
 *
 * @author Dzer <matthieu@mnapoli.fr>
 */
class Container
{
    private static $definitions = [];

    private static $instances = [];

    private static $classAlias = [];

    /**
     * getInstance
     *
     * @param $className
     * @param null $params
     * @return mixed
     * @throws \Exception
     */
    public static function getInstance($className, $params = null)
    {
        $keyName = $className;
        if (!empty($params['_prefix'])) {
            $keyName .= $params['_prefix'];
        }
        if (isset(self::$instances[$keyName])) {
            return self::$instances[$keyName];
        }
        if (!class_exists($className)) {
            throw new \Exception("no class {$className}");
        }
        if (empty($params)) {
            self::$instances[$keyName] = new $className();
        } else {
            self::$instances[$keyName] = new $className($params);
        }
        return self::$instances[$keyName];
    }

    /**
     * addDefinitions
     *
     * @param array $definitions
     * @return array
     */
    public static function addDefinitions(array $definitions)
    {
        return self::$definitions += $definitions;
    }

    public static function get($alias)
    {
        if (isset(self::$classAlias[$alias]) && isset(self::$instances[self::$classAlias[$alias]])
            && is_object(self::$instances[self::$classAlias[$alias]])
        ) {
            return self::$instances[self::$classAlias[$alias]];
        }

        if (isset(self::$definitions[$alias]) && is_callable(self::$definitions[$alias])) {
            $callable = self::$definitions[$alias];
            self::$classAlias[$alias] = get_class($callable());
            return self::$instances[self::$classAlias[$alias]];
        }
        throw new \Exception("No entry or class found for '$alias'");
    }

    public static function getInstances(){
        return self::$instances;
    }

    public function set($name, $value)
    {

    }

    public function has($name)
    {

    }

    public function make($name, array $parameters = [])
    {

    }

    public function call($callable, array $parameters = [])
    {

    }


}
