<?php

namespace Mll\Request;
use Mll\Mll;

/**
 * 请求基础类
 *
 * @package Mll\Request
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
abstract class Base implements IRequest
{
    protected $config = [
        // 默认模块名
        'default_module' => 'index',
        // 禁止访问模块
        'deny_module_list' => ['config'],
        // 默认控制器名
        'default_controller' => 'Index',
        // 默认操作名
        'default_action' => 'index',
        //兼容pathInfo变量
        'path_info_var' => 'r',
        //默认全局过滤函数
        'default_filter' => '',
        //请求唯一key
        'request_id_key' => 'X-Request-Id',
        //请求时间key
        'request_time_key' => 'X-Request-Time',
        // 表单请求类型伪装变量
        'var_method' => '_method',
    ];

    /**
     * 模块.
     *
     * @var
     */
    protected $module;

    /**
     * 控制器.
     *
     * @var
     */
    protected $controller;

    /**
     * 方法.
     *
     * @var
     */
    protected $action;

    /**
     * 请求方式.
     *
     * @var
     */
    protected $method;

    /**
     * url.
     *
     * @var
     */
    protected $url;

    //php:://input
    protected $input;

    /**
     * 全局参数过滤.
     *
     * @var
     */
    protected $filter;

    /**
     * 模板方式.
     *
     * @var
     */
    protected $viewMode;

    /**
     * 模板文件.
     *
     * @var
     */
    protected $tplFile;

    /**
     * 请求时间.
     *
     * @var
     */
    protected $requestTime;

    /**
     * @var
     */
    private $scriptUrl;

    /**
     * 基础Url
     * @var
     */
    private $baseUrl;

    /**
     * pathInfo
     * @var
     */
    private $pathInfo;

    /**
     * @var array 请求参数
     */
    protected $param = [];
    protected $get = [];
    protected $post = [];
    protected $request = [];
    protected $route = [];
    protected $put;
    protected $session = [];
    protected $file = [];
    protected $cookie = [];
    protected $server = [];
    protected $header = [];

    public function __construct($config = [])
    {
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }
        if (is_null($this->filter)) {
            $this->filter = $this->config['default_filter'];
        }
        // 保存 php://input
        $this->input = file_get_contents('php://input');
        //设置请求时间
        $this->setRequestTime();
    }

    /**
     * 初始化request.
     *
     * @param string $module 模块
     * @param string $controller 控制器
     * @param string $action 方法
     * @param array $params 请求参数
     * @param null $viewMode 视图模型
     */
    public function init($module, $controller, $action, array $params, $viewMode = null)
    {
        if ($module) {
            $this->module = $module;
        } else {
            $this->module = $this->config['default_module'];
        }

        if (isset($controller)) {
            $this->controller = $controller;
        } else {
            $this->controller = $this->config['default_controller'];
        }

        if (isset($action)) {
            $this->action = $action;
        } else {
            $this->action = $this->config['default_action'];
        }

        $this->param = $params;
        $this->viewMode = $viewMode;

        $this->tplFile = $this->module . DS . \str_replace('\\', DS, $this->controller) . DS . $this->action . '.php';

        $this->setRequestId();
    }

    /**
     * 设置请求唯一id
     *
     * @param string $requestId 请求唯一id
     *
     * @return string
     */
    public function setRequestId($requestId = null)
    {
        if (empty($requestId)) {
            $requestId = self::getRequestId(true);
        }
        $this->header($this->config['request_id_key'], $requestId);
        //todo 添加响应头
        header("{$this->config['request_id_key']}: $requestId");

        return $requestId;
    }

    /**
     * 获取请求id.
     *
     * @param bool $autoMake 当请求id为空时是否设置请求id
     *
     * @return mixed|null|string
     */
    public function getRequestId($autoMake = false)
    {
        $requestId = $this->header($this->config['request_id_key']);
        if ($autoMake && empty($requestId)) {
            $requestId = self::makeRequestId();
        }

        return $requestId;
    }

    /**
     * 生成请求id.
     *
     * @return string
     */
    public static function makeRequestId()
    {
        return sha1(uniqid('_' . mt_rand(1, 1000000), true));
    }

    /**
     * 将不同server的传输数据统一格式.
     *
     * @param string $pathInfo pathInfo
     * @param mixed $params 参数
     * @return void
     */
    abstract public function parse($pathInfo = null, $params = null);

    /**
     * 获取模块.
     *
     * @return mixed
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * 获取调用方法.
     *
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * 获取调用方法.
     *
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * 获取请求方法.
     *
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * 设置获取获取GET参数.
     *
     * @param string|array $name 变量名
     * @param mixed $default 默认值
     * @param string|array $filter 过滤方法
     *
     * @return mixed
     */
    public function get($name = '', $default = null, $filter = '')
    {
        if (empty($this->get)) {
            $this->get = $_GET;
        }
        if (is_array($name)) {
            $this->param = [];

            return $this->get = array_merge($this->get, $name);
        }

        return $this->input($this->get, $name, $default, $filter);
    }

    /**
     * 设置获取获取POST参数.
     *
     * @param string $name 变量名
     * @param mixed $default 默认值
     * @param string|array $filter 过滤方法
     *
     * @return mixed
     */
    public function post($name = '', $default = null, $filter = '')
    {
        if (empty($this->post)) {
            $content = $this->input;
            if (empty($_POST) && 'application/json' == $this->contentType()) {
                $this->post = (array)json_decode($content, true);
            } else {
                $this->post = $_POST;
            }
        }
        if (is_array($name)) {
            $this->param = [];

            return $this->post = array_merge($this->post, $name);
        }

        return $this->input($this->post, $name, $default, $filter);
    }

    /**
     * 当前请求 HTTP_CONTENT_TYPE.
     *
     * @return string
     */
    public function contentType()
    {
        $contentType = $this->server('CONTENT_TYPE');
        if ($contentType) {
            list($type) = explode(';', $contentType);

            return trim($type);
        }

        return '';
    }

    /**
     * 获取server参数.
     *
     * @param string|array $name 数据名称
     * @param string $default 默认值
     * @param string|array $filter 过滤方法
     *
     * @return mixed
     */
    public function server($name = '', $default = null, $filter = '')
    {
        if (empty($this->server)) {
            $this->server = $_SERVER;
        }
        if (is_array($name)) {
            return $this->server = array_merge($this->server, $name);
        }

        return $this->input($this->server, false === $name ? false : strtoupper($name), $default, $filter);
    }

    /**
     * 设置或者获取当前的Header.
     *
     * @param string|array $name header名称
     * @param string $default 默认值
     *
     * @return string
     */
    public function header($name = '', $default = null)
    {
        if (empty($this->header)) {
            $header = [];
            if (function_exists('apache_request_headers') && $result = apache_request_headers()) {
                $header = $result;
            } else {
                $server = $this->server ?: $_SERVER;
                foreach ($server as $key => $val) {
                    if (0 === strpos($key, 'HTTP_')) {
                        $key = str_replace('_', '-', strtolower(substr($key, 5)));
                        $header[$key] = $val;
                    }
                }
                if (isset($server['CONTENT_TYPE'])) {
                    $header['content-type'] = $server['CONTENT_TYPE'];
                }
                if (isset($server['CONTENT_LENGTH'])) {
                    $header['content-length'] = $server['CONTENT_LENGTH'];
                }
            }
            $this->header = array_change_key_case($header);
        }
        if (is_array($name)) {
            return $this->header = array_merge($this->header, $name);
        }
        if ('' === $name) {
            return $this->header;
        }
        $name = str_replace('_', '-', strtolower($name));

        return isset($this->header[$name]) ? $this->header[$name] : $default;
    }

    /**
     * 设置获取获取PUT参数.
     *
     * @param string|array $name 变量名
     * @param mixed $default 默认值
     * @param string|array $filter 过滤方法
     *
     * @return mixed
     */
    public function put($name = '', $default = null, $filter = '')
    {
        if (is_null($this->put)) {
            $content = $this->input;
            if ('application/json' == $this->contentType()) {
                $this->put = (array)json_decode($content, true);
            } else {
                parse_str($content, $this->put);
            }
        }
        if (is_array($name)) {
            $this->param = [];

            return $this->put = is_null($this->put) ? $name : array_merge($this->put, $name);
        }

        return $this->input($this->put, $name, $default, $filter);
    }

    /**
     * 设置获取获取DELETE参数.
     *
     * @param string|array $name 变量名
     * @param mixed $default 默认值
     * @param string|array $filter 过滤方法
     *
     * @return mixed
     */
    public function delete($name = '', $default = null, $filter = '')
    {
        return $this->put($name, $default, $filter);
    }

    /**
     * 设置获取获取PATCH参数.
     *
     * @param string|array $name 变量名
     * @param mixed $default 默认值
     * @param string|array $filter 过滤方法
     *
     * @return mixed
     */
    public function patch($name = '', $default = null, $filter = '')
    {
        return $this->put($name, $default, $filter);
    }

    /**
     * 获取变量 支持过滤和默认值
     *
     * @param array $data 数据源
     * @param string|false $name 字段名
     * @param mixed $default 默认值
     * @param string|array $filter 过滤函数
     *
     * @return mixed
     */
    public function input($data = [], $name = '', $default = null, $filter = '')
    {
        if (false === $name) {
            // 获取原始数据
            return $data;
        }
        $name = (string)$name;
        if ('' != $name) {
            // 解析name
            if (strpos($name, '/')) {
                list($name, $type) = explode('/', $name);
            } else {
                $type = 's';
            }
            // 按.拆分成多维数组进行判断
            foreach (explode('.', $name) as $val) {
                if (isset($data[$val])) {
                    $data = $data[$val];
                } else {
                    // 无输入数据，返回默认值
                    return $default;
                }
            }
            if (is_object($data)) {
                return $data;
            }
        }

        // 解析过滤器
        if (is_null($filter)) {
            $filter = [];
        } else {
            $filter = $filter ?: $this->filter;
            if (is_string($filter)) {
                $filter = explode(',', $filter);
            } else {
                $filter = (array)$filter;
            }
        }

        $filter[] = $default;
        if (is_array($data)) {
            array_walk_recursive($data, [$this, 'filterValue'], $filter);
            reset($data);
        } else {
            $this->filterValue($data, $name, $filter);
        }

        if (isset($type) && $data !== $default) {
            // 强制类型转换
            $this->typeCast($data, $type);
        }

        return $data;
    }

    /**
     * 强制类型转换.
     *
     * @param string $data
     * @param string $type
     *
     * @return void
     */
    private function typeCast(&$data, $type)
    {
        switch (strtolower($type)) {
            // 数组
            case 'a':
                $data = (array)$data;
                break;
            // 数字
            case 'd':
                $data = (int)$data;
                break;
            // 浮点
            case 'f':
                $data = (float)$data;
                break;
            // 布尔
            case 'b':
                $data = (bool)$data;
                break;
            // 字符串
            case 's':
            default:
                if (is_scalar($data)) {
                    $data = (string)$data;
                } else {
                    throw new \InvalidArgumentException('variable type error：' . gettype($data));
                }
        }
    }

    /**
     * 获取request变量.
     *
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string|array $filter 过滤方法
     *
     * @return mixed
     */
    public function request($name = '', $default = null, $filter = '')
    {
        if (empty($this->request)) {
            $this->request = $_REQUEST;
        }
        if (is_array($name)) {
            $this->param = [];

            return $this->request = array_merge($this->request, $name);
        }

        return $this->input($this->request, $name, $default, $filter);
    }

    /**
     * 获取session数据.
     *
     * @param string|array $name 数据名称
     * @param string $default 默认值
     * @param string|array $filter 过滤方法
     *
     * @return mixed
     */
    public function session($name = '', $default = null, $filter = '')
    {
        if (empty($this->session)) {
            $this->session = Mll::app()->session->get();
        }
        if (is_array($name)) {
            return $this->session = array_merge($this->session, $name);
        }
        return $this->input($this->session, $name, $default, $filter);
    }

    /**
     * 递归过滤给定的值
     *
     * @param mixed $value 键值
     * @param mixed $key 键名
     * @param array $filters 过滤方法+默认值
     *
     * @return mixed
     */
    private function filterValue(&$value, $key, $filters)
    {
        $default = array_pop($filters);
        foreach ($filters as $filter) {
            if (is_callable($filter)) {
                // 调用函数或者方法过滤
                $value = call_user_func($filter, $value);
            } elseif (is_scalar($value)) {
                if (strpos($filter, '/')) {
                    // 正则过滤
                    if (!preg_match($filter, $value)) {
                        // 匹配不成功返回默认值
                        $value = $default;
                        break;
                    }
                } elseif (!empty($filter)) {
                    // filter函数不存在时, 则使用filter_var进行过滤
                    // filter为非整形值时, 调用filter_id取得过滤id
                    $value = filter_var($value, is_int($filter) ? $filter : filter_id($filter));
                    if (false === $value) {
                        $value = $default;
                        break;
                    }
                }
            }
        }

        return $this->filterExp($value);
    }

    /**
     * 过滤表单中的表达式.
     *
     * @param string $value
     *
     * @return bool
     */
    public function filterExp(&$value)
    {
        // 过滤查询特殊字符
        if (is_string($value) &&
            preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT BETWEEN|' .
                'NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN)$/i', $value)
        ) {
            $value .= ' ';
        }

        return true;
        // TODO 其他安全过滤
    }

    /**
     * @param null $time
     *
     * @return bool
     */
    public function setRequestTime($time = null)
    {
        if (!empty($this->requestTime)) {
            return false;
        }
        if (empty($time)) {
            if (!empty($_REQUEST['REQUEST_TIME_FLOAT'])) {
                $time = $_REQUEST['REQUEST_TIME_FLOAT'];
            } else {
                $time = microtime(true);
            }
        }
        $this->requestTime = $time;
        $this->header($this->config['request_time_key'], $time);

        return true;
    }

    /**
     * 获取请求开始时间.
     *
     * @param bool $clear
     *
     * @return float
     */
    public function getRequestTime($clear = false)
    {
        $time = $this->requestTime;
        if ($clear) {
            $this->requestTime = null;
        }

        return $time;
    }

    /**
     * 解析额外参数.
     *
     * @param $url
     * @param array $var
     */
    protected function parseUrlParams($url, &$var = [])
    {
        if ($url) {
            preg_replace_callback('/(\w+)\|([^\|]+)/', function ($match) use (&$var) {
                $var[$match[1]] = strip_tags($match[2]);
            }, $url);
        }
        // 设置当前请求的参数
        $this->route($var);
    }

    /**
     * 获取获取当前请求的参数.
     *
     * @param string|array $name 变量名
     * @param mixed $default 默认值
     * @param string|array $filter 过滤方法
     *
     * @return mixed
     */
    public function param($name = '', $default = null, $filter = '')
    {
        if (empty($this->param)) {
            $method = $this->method(true);
            // 自动获取请求变量
            switch ($method) {
                case 'POST':
                    $vars = $this->post(false);
                    break;
                case 'PUT':
                case 'DELETE':
                case 'PATCH':
                    $vars = $this->put(false);
                    break;
                default:
                    $vars = [];
            }
            // 当前请求参数和URL地址中的参数合并
            $this->param = array_merge($this->get(false), $vars, $this->route(false));
        }

        return $this->input($this->param, $name, $default, $filter);
    }

    /**
     * 当前的请求类型.
     *
     * @param bool $method true 获取原始请求类型
     *
     * @return string
     */
    public function method($method = false)
    {
        if (true === $method) {
            // 获取原始请求类型
            return isset($this->server['REQUEST_METHOD']) ?
                $this->server['REQUEST_METHOD'] : $_SERVER['REQUEST_METHOD'];
        } elseif (!$this->method) {
            if (isset($_POST[$this->config['var_method']])) {
                $this->method = strtoupper($_POST[$this->config['var_method']]);
                $this->{$this->method}($_POST);
            } elseif (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
                $this->method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
            } else {
                $this->method = isset($this->server['REQUEST_METHOD']) ?
                    $this->server['REQUEST_METHOD'] : $_SERVER['REQUEST_METHOD'];
            }
        }

        return $this->method;
    }

    /**
     * 设置获取获取路由参数.
     *
     * @param string|array $name 变量名
     * @param mixed $default 默认值
     * @param string|array $filter 过滤方法
     *
     * @return mixed
     */
    public function route($name = '', $default = null, $filter = '')
    {
        if (is_array($name)) {
            $this->param = [];

            return $this->route = array_merge($this->route, $name);
        }

        return $this->input($this->route, $name, $default, $filter);
    }

    /**
     * 获取pathInfo.
     *
     * @return string
     */
    public function getPathInfo()
    {
        if ($this->pathInfo === null) {
            $this->pathInfo = $this->resolvePathInfo();
        }

        if (isset($_GET[$this->config['path_info_var']])) {
            $this->pathInfo = $_GET[$this->config['path_info_var']];
            unset($_GET[$this->config['path_info_var']]);
        }

        return $this->pathInfo;
    }

    /**
     * 获取当前Url.
     *
     * @return mixed|string
     */
    public function getUrl()
    {
        if ($this->url === null) {
            $this->url = $this->resolveRequestUri();
        }

        return $this->url;
    }

    /**
     * 解析当前请求的URL的请求URI部分.
     *
     * @return mixed|string
     *
     * @throws \Exception
     */
    protected function resolveRequestUri()
    {
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // IIS
            $requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
            if ($requestUri !== '' && $requestUri[0] !== '/') {
                $requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
            }
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0 CGI
            $requestUri = $_SERVER['ORIG_PATH_INFO'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $requestUri .= '?' . $_SERVER['QUERY_STRING'];
            }
        } else {
            throw new \Exception('Unable to determine the request URI.');
        }

        return $requestUri;
    }

    /**
     * 解析PathInfo
     *
     * @return string
     * @throws \Exception
     */
    protected function resolvePathInfo()
    {
        $pathInfo = $this->getUrl();

        if (($pos = strpos($pathInfo, '?')) !== false) {
            $pathInfo = substr($pathInfo, 0, $pos);
        }

        $pathInfo = urldecode($pathInfo);

        // try to encode in UTF8 if not so
        // http://w3.org/International/questions/qa-forms-utf-8.html
        if (!preg_match('%^(?:
            [\x09\x0A\x0D\x20-\x7E]              # ASCII
            | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
            | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
            | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
            | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
            )*$%xs', $pathInfo)
        ) {
            $pathInfo = utf8_encode($pathInfo);
        }

        $scriptUrl = $this->getScriptUrl();
        $baseUrl = $this->getBaseUrl();
        if (strpos($pathInfo, $scriptUrl) === 0) {
            $pathInfo = substr($pathInfo, strlen($scriptUrl));
        } elseif ($baseUrl === '' || strpos($pathInfo, $baseUrl) === 0) {
            $pathInfo = substr($pathInfo, strlen($baseUrl));
        } elseif (isset($_SERVER['PHP_SELF']) && strpos($_SERVER['PHP_SELF'], $scriptUrl) === 0) {
            $pathInfo = substr($_SERVER['PHP_SELF'], strlen($scriptUrl));
        } else {
            throw new \Exception('Unable to determine the path info of the current request.');
        }

        if (substr($pathInfo, 0, 1) === '/') {
            $pathInfo = substr($pathInfo, 1);
        }

        return (string)$pathInfo;
    }

    /**
     * 返回脚本url
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function getScriptUrl()
    {
        if ($this->scriptUrl === null) {
            $scriptFile = $this->getScriptFile();
            $scriptName = basename($scriptFile);
            if (isset($_SERVER['SCRIPT_NAME']) && basename($_SERVER['SCRIPT_NAME']) === $scriptName) {
                $this->scriptUrl = $_SERVER['SCRIPT_NAME'];
            } elseif (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) === $scriptName) {
                $this->scriptUrl = $_SERVER['PHP_SELF'];
            } elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName) {
                $this->scriptUrl = $_SERVER['ORIG_SCRIPT_NAME'];
            } elseif (isset($_SERVER['PHP_SELF'])
                && ($pos = strpos($_SERVER['PHP_SELF'], '/' . $scriptName)) !== false
            ) {
                $this->scriptUrl = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $scriptName;
            } elseif (!empty($_SERVER['DOCUMENT_ROOT']) && strpos($scriptFile, $_SERVER['DOCUMENT_ROOT']) === 0) {
                $this->scriptUrl = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $scriptFile));
            } else {
                throw new \Exception('Unable to determine the entry script URL.');
            }
        }

        return $this->scriptUrl;
    }

    /**
     * 返回脚本文件路径.
     * 默认将简单地返回 `$_SERVER['SCRIPT_FILENAME']`.
     *
     * @return string 脚本文件路径
     *
     * @throws \Exception
     */
    public function getScriptFile()
    {
        return $_SERVER['SCRIPT_FILENAME'];
    }

    /**
     * 返回应用程序的相对URL。
     *
     * @return string 应用程序的相对URL
     *
     * @see setScriptUrl()
     */
    public function getBaseUrl()
    {
        if ($this->baseUrl === null) {
            $this->baseUrl = rtrim(dirname($this->getScriptUrl()), '\\/');
        }

        return $this->baseUrl;
    }

    /**
     * 当前是否Ajax请求
     *
     * @param bool $ajax true 获取原始ajax请求
     *
     * @return bool
     */
    public function isAjax($ajax = false)
    {
        $value = $this->server('HTTP_X_REQUESTED_WITH', '', 'strtolower');
        $result = ('xmlhttprequest' == $value) ? true : false;

        return $result;
    }
}
