<?php

namespace Mll\Core;

/**
 * 服务容器（简化版的 service Locator服务定位器 和 DI依赖注入）
 *
 * @author Dzer <dz@mnapoli.fr>
 */
class Container
{
    /**
     * 依赖的定义
     * @var array
     */
    private static $definitions = [];

    /**
     * 对象
     * @var array
     */
    private static $instances = [];

    /**
     * 别名与实例关系
     * @var array
     */
    private static $classAlias = [];

    /**
     * 依赖关系
     * @var array
     */
    private static $dependencies = [];

    /**
     * 依赖信息
     * @var array
     */
    private $reflections = [];

    /**
     * 实例化（单例）
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
     * 添加服务配置文件
     *
     * @param array $definitions
     * @return array
     */
    public static function addDefinitions(array $definitions)
    {

        return self::$definitions += $definitions;
    }

    /**
     * 获取实例
     *
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public static function get($name)
    {
        if (isset(self::$classAlias[$name]) && isset(self::$instances[self::$classAlias[$name]])
            && is_object(self::$instances[self::$classAlias[$name]])
        ) {
            return self::$instances[self::$classAlias[$name]];
        }

        if (isset(self::$definitions[$name]) && is_callable(self::$definitions[$name])) {
            $callable = self::$definitions[$name];
            self::$classAlias[$name] = get_class($callable());
            return self::$instances[self::$classAlias[$name]];
        }
        throw new \Exception("No entry or class found for '$name'");
    }

    public static function getInstances()
    {
        return self::$instances;
    }

    /**
     * 设置容器变量
     *
     * @param string $name 别名
     * @param string|array|object|callable $value 值可以是类名、实例、可调用结构、数组
     * @param bool $is_cover 是否覆盖
     * @return bool
     * @throws \Exception
     */
    public function set($name, $value, $is_cover = true)
    {
        if (!$is_cover && isset(self::$definitions[$name])) {
            throw new \Exception("this {$name} on Container is existed");
        }

        // 规范化 $definition 并写入 $_definitions[$class]
        self::$definitions[$name] = self::normalizeDefinition($name, $value);

        // 将构造函数参数写入 $_params[$class]
        $this->_params[$class] = $params;

        // 删除$_singletons[$class]
        unset(self::$instances[$class]);
        return $this;


        self::$definitions[$name] = $value;
        return true;
    }

    /**
     * 设置容器变量
     *
     * @param string $name 别名
     * @param string|array|object|callable $definition 值可以是类名、实例、可调用结构、数组
     * @return mixed
     * @throws \Exception
     */
    protected static function normalizeDefinition($name, $definition)
    {
        // $definition 是空的转换成 ['class' => $class] 形式
        if (empty($definition)) {
            throw new \Exception("definition {$name} can not empty");

            // $definition 是字符串，转换成 ['class' => $definition] 形式
        } elseif (is_string($definition)) {
            return $definition;

            // $definition 是PHP callable 或对象，则直接将其作为依赖的定义
        } elseif (is_callable($definition, true) || is_object($definition)) {
            return $definition;

            // $definition 是数组则确保该数组定义了 class 元素
        } elseif (is_array($definition)) {
            if (!isset($definition['class'])) {
                if (strpos($class, '\\') !== false) {
                    $definition['class'] = $class;
                } else {
                    throw new \Exception(
                        "A class definition requires a \"class\" member.");
                }
            }
            return $definition;
            // 这也不是，那也不是，那就抛出异常算了
        } else {
            throw new \Exception(
                "Unsupported definition type for \"$class\": "
                . gettype($definition));
        }
    }

    /**
     * 测试名称是否存在容器中
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        if (isset(self::$instances[self::$classAlias[$name]])) {
            return true;
        }
        return false;
    }

    /**
     * 创建一个实例
     * 暂时只做一层
     *
     * @param $name
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    public function make($name, array $parameters = [])
    {
        if (!isset(self::$definitions[$name])) {
            throw new \Exception("name not found for '$name'");
        }
        $callable = self::$definitions[$name];

        if (!is_callable($callable)) {
            throw new \Exception("this {$name}'s value cannot be called as a function");
        }

        // 按数组键排序，因为call_user_func_array忽略数字键
        ksort($args);
        //通过构造函数来创建依赖(依赖清晰，通过反射，但依赖太多构造函数参数太庞大)
        //或者通过类的属性来创建依赖（需要注释@property 通过反射解析注释,而且注释没有保障）


        return call_user_func_array($callable, $args);
    }

    public function call($callable, array $parameters = [])
    {

    }

    /**
     * 分析依赖关系
     *
     * @param $class
     * @return array
     */
    protected function getDependencies($class)
    {
        // 如果已经缓存了其依赖信息，直接返回缓存中的依赖信息
        if (isset($this->reflections[$class])) {
            return [$this->reflections[$class], $this->_dependencies[$class]];
        }

        $dependencies = [];

        // 使用PHP5 的反射机制来获取类的有关信息，主要就是为了获取依赖信息
        $reflection = new ReflectionClass($class);

        // 通过类的构建函数的参数来了解这个类依赖于哪些单元
        $constructor = $reflection->getConstructor();
        if ($constructor !== null) {
            foreach ($constructor->getParameters() as $param) {
                if ($param->isDefaultValueAvailable()) {

                    // 构造函数如果有默认值，将默认值作为依赖。即然是默认值了，
                    // 就肯定是简单类型了。
                    $dependencies[] = $param->getDefaultValue();
                } else {
                    $c = $param->getClass();

                    // 构造函数没有默认值，则为其创建一个引用。
                    // 就是前面提到的 Instance 类型。
                    $dependencies[] = Instance::of($c === null ? null :
                        $c->getName());
                }
            }
        }

        // 将 ReflectionClass 对象缓存起来
        $this->_reflections[$class] = $reflection;

        // 将依赖信息缓存起来
        $this->_dependencies[$class] = $dependencies;

        return [$reflection, $dependencies];
    }


}
