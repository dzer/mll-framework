<?php

namespace Mll\View;

abstract class Base implements IView
{

    protected $data = [];

    /**
     * 模板变量赋值
     *
     * @access public
     * @param mixed $name  变量名
     * @param mixed $value 变量值
     * @return $this
     */
    public function assign($name, $value = '')
    {
        if (is_array($name)) {
            $this->data = array_merge($this->data, $name);
        } else {
            $this->data[$name] = $value;
        }
        return $this;
    }

    /**
     * 渲染模板并输出
     *
     * @param $template
     * @param array $params
     * @return mixed
     */
    abstract public function display($template, $params = []);

    /**
     * 返回渲染模板内容
     *
     * @param $template
     * @param array $params
     * @return string
     */
    public function fetch($template, $params = [])
    {
        ob_start();
        $this->display($template, $params);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    /**
     * 模板变量赋值
     * @access public
     * @param string    $name  变量名
     * @param mixed     $value 变量值
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * 取得模板显示变量的值
     * @access protected
     * @param string $name 模板变量
     * @return mixed
     */
    public function __get($name)
    {
        return $this->data[$name];
    }

    /**
     * 检测模板变量是否设置
     * @access public
     * @param string $name 模板变量名
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

}