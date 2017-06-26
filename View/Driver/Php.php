<?php

namespace Mll\View\Driver;

use Mll\Mll;
use Mll\View\Base;

class Php extends Base
{
    private $config = [
        'template_path' => '/template',
    ];

    public function __construct()
    {
        $this->config = array_merge($this->config, Mll::app()->config->get('view', []));
    }

    /**
     * 渲染模板并输出
     *
     * @param $template
     * @param array $params
     * @return null
     * @throws \Exception
     */
    public function display($template, $params = [])
    {
        if (!empty($params)) {
            $this->data = array_merge($this->data, $params);
        }

        if ('' == pathinfo($template, PATHINFO_EXTENSION)) {
            // 获取模板文件名
            $template = $this->parseTemplate($template);
        }
        // 模板不存在 抛出异常
        if (!is_file($template)) {
            throw new \Exception('template not exists:' . $template, $template);
        }

        if (!empty($this->data) && is_array($this->data)) {
            extract($this->data);
        }

        include "{$template}";
        return null;
    }

    /**
     * 自动定位模板文件
     * @access private
     * @param string $template 模板文件规则
     * @return string
     */
    private function parseTemplate($template)
    {
        if (empty($this->config['template_path'])) {
            $this->config['template_path'] = '/template';
        }
        $request = Mll::app()->request;
        $path = ROOT_PATH . $this->config['template_path'];
        if (strpos($template, '/') === false) {
            $template = $request->getModule() . DS . $request->getController() . DS . $template;
        }

        return $path . DS . $template . '.php';
    }


}
