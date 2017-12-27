<?php

namespace Mll\View\Driver;

use Mll\Common\Dir;
use Mll\Mll;
use Mll\View\Base;

/**
 * Smarty精简版(美乐乐特别版)
 *
 * @package Mll\View\Driver
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Smarty extends Base
{

    private $config = [
        'template_dir' => '/template', //模板目录
        'compile_dir' => '/runtime/temp/template_compile', //编译目录
    ];

    private $template_path = '';
    private $compile_path = '';
    private $foreach_mark = '';

    public function __construct()
    {
        $this->config = array_merge($this->config, Mll::app()->config->get('view', []));
    }


    /**
     * 渲染模板并输出
     *
     * @param string $template
     * @param array $params
     * @throws \Exception
     * @return void
     */
    public function display($template, $params = [])
    {
        if (!empty($params)) {
            $this->data = array_merge($this->data, $params);
        }

        $this->parseTemplate($template);

        $out = $this->makeCompiled();

        $out = $this->replaceSourceServerHost($out);

        echo $out;
    }

    /**
     * 拼接模板路径
     *
     * @param $template
     * @throws \Exception
     */
    private function parseTemplate($template)
    {
        $request = Mll::app()->request;
        if (strpos($template, DS) === false) {
            $template = $request->getModule() . DS . $request->getController() . DS . $template;
        }
        $template_path_info = pathinfo($template);
        //判断是否直接传入的路径
        if ($template_path_info['extension'] == '') {
            $this->template_path = ROOT_PATH . $this->config['template_dir'] . DS . $template . '.dwt';
        } else {
            $template = ltrim($template, '/');
            $this->template_path = ROOT_PATH . $this->config['template_dir'] . DS . $template;
        }

        $template_path_info['dirname'] = ltrim($template_path_info['dirname'], DS);
        // 模板不存在 抛出异常
        if (!is_file($this->template_path)) {
            throw new \Exception('template not exists:' . $this->template_path);
        }
        $this->compile_path = ROOT_PATH . $this->config['compile_dir'] . DS . $template_path_info['dirname']
            . DS . $template_path_info['filename'] . '.'
            . pathinfo($this->template_path, PATHINFO_EXTENSION) . '.php';
    }

    /**
     * 替换资源服务器域名
     *
     * @param $out
     * @return mixed
     * @throws \Exception
     */
    private function replaceSourceServerHost($out)
    {
        $img_servers = Mll::app()->config->get('source_server_host');
        if (empty($img_servers)) {
            throw new \Exception('resource server is empty');
        }
        $img = $img_servers[0];
        $pattern = '/src=["|\'| ]{0,}' . preg_quote($img, '/') . '\/(.+?\.(gif|jpg|jpeg|bmp|png))/i';
        preg_match_all($pattern, $out, $arr);
        if (!empty($arr[1])) {
            $arr = array_unique($arr[1]);
            $search_arr = $replace_arr = [];
            foreach ($arr as $row) {
                $search_arr[] = $img . '/' . $row;
                $replace_arr[] = $this->imgServerHost($row);
            }
            $out = str_replace($search_arr, $replace_arr, $out);
        }
        return $out;
    }

    /**
     * imgServerHost
     *
     * @param $img_src
     * @return string
     */
    private function imgServerHost($img_src)
    {
        $img_servers = Mll::app()->config->get('source_server_host');
        $index = crc32($img_src) % count($img_servers);
        return "{$img_servers[$index]}/{$img_src}";
    }


    /**
     * 编译模板函数
     *
     * @return string
     * @throws \Exception
     */
    private function makeCompiled()
    {
        //如果编译文件存在且修改时间在模板文件修改时间之后，就直接require编译文件
        if (file_exists($this->compile_path)) {
            $compile_file_stat = @stat($this->compile_path);
            $template_file_stat = @stat($this->template_path);
            if ($template_file_stat['mtime'] <= $compile_file_stat['mtime']) {
                return $this->requireFile($this->compile_path);
            }
        }

        $source = $this->fetchStr(file_get_contents($this->template_path));
        $compile_dir = dirname($this->compile_path);
        if (!is_dir($compile_dir)) {
            Dir::make($compile_dir, 0777);
        }
        if (file_put_contents($this->compile_path, $source) === false) {
            throw new \Exception('can\'t write compiled template:' . $this->compile_path);
        }
        return $this->evalFuc($source);
    }

    /**
     * 处理字符串函数
     *
     * @param  string $source 模板文件
     * @return  string
     */
    private function fetchStr($source)
    {
        return preg_replace_callback("/{([^\}\{\n]*)}/", function ($match) {
            return $this->select($match[1]);
        }, $source);
    }

    /**
     * 处理{}标签
     *
     * @param   string $tag
     * @return  string
     */
    public function select($tag)
    {
        $tag = stripslashes(trim($tag));

        if (empty($tag)) {
            return '{}';
        } elseif ($tag{0} == '*' && substr($tag, -1) == '*') {
            return '';
        } elseif ($tag{0} == '$') {
            return '<?php echo ' . $this->getVal(substr($tag, 1)) . '; ?>';
        } elseif ($tag{0} == '/') {
            switch (substr($tag, 1)) {
                case 'if':
                    return '<?php endif; ?>';
                    break;
                case 'foreach':
                    if ($this->foreach_mark == 'foreachelse') {
                        return '<?php endif; unset($_from); ?>';
                    } else {
                        return '<?php endforeach; endif; unset($_from); ?>';
                    }
                    break;

                case 'literal':
                    return '';
                    break;

                default:
                    return '{' . $tag . '}';
                    break;
            }
        } else {
            $tag_array = explode(' ', $tag);
            $tag_sel = array_shift($tag_array);
            switch ($tag_sel) {
                case 'if':
                    return $this->compileIfTag(substr($tag, 3));
                    break;
                case 'else':
                    return '<?php else: ?>';
                    break;
                case 'elseif':
                    return $this->compileIfTag(substr($tag, 7), true);
                    break;
                case 'foreachelse':
                    $this->foreach_mark = 'foreachelse';
                    return '<?php endforeach; else: ?>';
                    break;
                case 'foreach':
                    $this->foreach_mark = 'foreach';
                    return $this->compileForeachStart(substr($tag, 8));
                    break;
                case 'assign':
                    $t = $this->getPara(substr($tag, 7), 0);
                    if ($t['value']{0} == '$') {
                        /* 如果传进来的值是变量，就不用用引号 */
                        $tmp = '$this->assign(\'' . $t['var'] . '\',' . $t['value'] . ');';
                    } else {
                        $tmp = '$this->assign(\'' . $t['var'] . '\',\'' . addcslashes($t['value'], "'") . '\');';
                    }
                    return '<?php ' . $tmp . ' ?>';
                    break;
                case 'include':
                    $t = $this->getPara(substr($tag, 8), 0);
                    return '<?php echo $this->fetch(' . "'$t[file]'" . '); ?>';
                    break;
                case 'include_script':
                    $t = $this->getPara(substr($tag, 15), 0);
                    $tmp_content = file_get_contents($this->config['template_dir'] . $t['file']);
                    $tmp_content = str_replace('\\', '\\\\', $tmp_content);
                    $tmp_content = str_replace("'", "\'", $tmp_content);
                    return '<?php echo (\'' . $tmp_content . '\') ?>';
                    break;
                case 'insert_scripts':
                    $t = $this->getPara(substr($tag, 15), 0);
                    return '<?php echo $this->smartyInsertScripts(' . $this->makeArray($t) . '); ?>';
                    break;
                case 'create_pages':
                    $t = $this->getPara(substr($tag, 13), 0);
                    return '<?php echo $this->smartyCreatePages(' . $this->makeArray($t) . '); ?>';
                    break;
                case 'literal':
                    return '';
                    break;
                case 'cycle':
                    $t = $this->getPara(substr($tag, 6), 0);
                    return '<?php echo $this->cycle(' . $this->makeArray($t) . '); ?>';
                    break;
                case 'html_options':
                    $t = $this->getPara(substr($tag, 13), 0);
                    return '<?php echo $this->htmlOptions(' . $this->makeArray($t) . '); ?>';
                    break;
                case 'html_select_date':
                    $t = $this->getPara(substr($tag, 17), 0);
                    return '<?php echo $this->htmlSelectDate(' . $this->makeArray($t) . '); ?>';
                    break;
                case 'html_radios':
                    $t = $this->getPara(substr($tag, 12), 0);
                    return '<?php echo $this->htmlRadios(' . $this->makeArray($t) . '); ?>';
                    break;
                case 'html_select_time':
                    $t = $this->getPara(substr($tag, 12), 0);
                    return '<?php echo $this->htmlSelectTime(' . $this->makeArray($t) . '); ?>';
                    break;
                default:
                    return '{' . $tag . '}';
                    break;
            }
        }
    }

    /**
     * 处理smarty标签中的变量标签
     *
     * @param   string $val
     * @return  bool
     */
    private function getVal($val)
    {
        if (strrpos($val, '[') !== false) {
            $val = preg_replace_callback("/\[([^\[\]]*)\]/is", function ($match) {
                return '.' . str_replace('$', '\$', $match[1]);
            }, $val);
        }

        if (strrpos($val, '|') !== false) {
            $moddb = explode('|', $val);
            $val = array_shift($moddb);
        }

        if (empty($val)) {
            return '';
        }

        if (strpos($val, '.$') !== false) {
            $all = explode('.$', $val);
            foreach ($all as $key => $val) {
                $all[$key] = $key == 0 ? $this->makeVar($val) : '[' . $this->makeVar($val) . ']';
            }
            $p = implode('', $all);
        } else {
            $p = $this->makeVar($val);
        }

        if (!empty($moddb)) {
            foreach ($moddb as $key => $mod) {
                $s = explode(':', $mod);
                switch ($s[0]) {
                    case 'escape':
                        $s[1] = trim($s[1], '"');
                        if ($s[1] == 'html') {
                            $p = 'htmlspecialchars(' . $p . ')';
                        } elseif ($s[1] == 'url') {
                            $p = 'urlencode(' . $p . ')';
                        } elseif ($s[1] == 'quotes') {
                            $p = 'strtr(' . $p . ',array(\'"\'=>\'&quot;\'))';
                        } else {
                            $p = 'htmlspecialchars(' . $p . ')';
                        }
                        break;

                    case 'nl2br':
                        $p = 'nl2br(' . $p . ')';
                        break;

                    case 'default':
                        $s[1] = $s[1]{0} == '$' ? $this->getVal(substr($s[1], 1)) : "'$s[1]'";
                        $p = 'empty(' . $p . ') ? ' . $s[1] . ' : ' . $p;
                        break;

                    case 'truncate':
                        $p = 'mb_substr(' . $p . ", 0, $s[1], 'UTF-8')";
                        break;
                    case 'strip_tags':
                        $p = 'strip_tags(' . $p . ')';
                        break;

                    //增加日期时间格式化,格式规则和PHP相同
                    case 'date_format':
                        $p = 'date(' . $s[1] . ',' . $p . ')';
                        break;
                    case 'mllthumb':
                    case 'phpthumb':
                        if ($s[3]) {
                            $p = '\Mll\Common\Common::mllThumb(' . $p . ',' . $s[1] . ',' . $s[2] . ',' . $s[3] . ')';
                        } else {
                            $p = '\Mll\Common\Common::mllThumb(' . $p . ',' . $s[1] . ',' . $s[2] . ')';
                        }
                        break;
                    case 'dwt_str_replace':
                        $oldstr = $p;
                        $count = $s[3];
                        if ($count) {
                            $p = 'str_replace(' . $s[1] . ',' . $s[2] . ',' . $oldstr . ',' . $count . ')';
                        } else {
                            $p = 'str_replace(' . $s[1] . ',' . $s[2] . ',' . $oldstr . ')';
                        }
                        break;
                    case 'dwt_int':
                        $p = 'intval(' . $p . ')';
                        break;
                    case 'var_dump':
                        $p = 'var_dump(' . $p . ')';
                        break;
                    default:
                        # code...
                        break;
                }
            }
        }

        return $p;
    }


    /**
     * 处理去掉$的字符串
     *
     * @param   string $val
     *
     * @return  bool
     */
    private function makeVar($val)
    {
        if (strrpos($val, '.') === false) {
            $p = '$this->data[\'' . $val . '\']';
        } else {
            $t = explode('.', $val);
            $_var_name = array_shift($t);
            if ($_var_name == 'smarty') {
                $p = $this->compileSmartyRef($t);
            } else {
                $p = '$this->data[\'' . $_var_name . '\']';
            }
            foreach ($t as $val) {
                $p .= '[\'' . $val . '\']';
            }
        }

        return $p;
    }

    /**
     * 处理insert外部函数/需要include运行的函数的调用数据
     *
     * @param   string $val
     * @param   int $type
     *
     * @return  array
     */
    private function getPara($val, $type = 1)
    {
        $para = array();
        $pa = $this->strTrim($val);
        foreach ($pa as $value) {
            if (strrpos($value, '=')) {
                list($a, $b) = explode('=', str_replace(array(' ', '"', "'", '&quot;'), '', $value));
                if ($b{0} == '$') {
                    if ($type) {
                        eval('$para[\'' . $a . '\']=' . $this->getVal(substr($b, 1)) . ';');
                    } else {
                        $para[$a] = $this->getVal(substr($b, 1));
                    }
                } else {
                    $para[$a] = $b;
                }
            }
        }

        return $para;
    }

    /**
     * 判断变量是否被注册并返回值
     *
     * @param   string $name
     *
     * @return  array|null
     */
    public function &getTemplateVars($name = null)
    {
        if (empty($name)) {
            return $this->data;
        } elseif (!empty($this->data[$name])) {
            return $this->data[$name];
        } else {
            return null;
        }
    }

    /**
     * 处理if标签
     *
     * @param   string $tag_args
     * @param   bool $elseif
     *
     * @return  string
     */
    private function compileIfTag($tag_args, $elseif = false)
    {
        preg_match_all('/\-?\d+[\.\d]+|\'[^\'|\s]*\'|"[^"|\s]*"|[\$\w\.]+|!==|===|==|!=|<>|<<|>>|<=|>='
            . '|&&|\|\||\(|\)|,|\!|\^|=|&|<|>|~|\||\%|\+|\-|\/|\*|\@|\S/', $tag_args, $match);
        $tokens = $match[0];
        // make sure we have balanced parenthesis
        $token_count = array_count_values($tokens);
        if (!empty($token_count['(']) && $token_count['('] != $token_count[')']) {
            // $this->_syntax_error('unbalanced parenthesis in if statement', E_USER_ERROR, __FILE__, __LINE__);
        }

        for ($i = 0, $count = count($tokens); $i < $count; $i++) {
            $token = &$tokens[$i];
            switch (strtolower($token)) {
                case 'eq':
                    $token = '==';
                    break;

                case 'ne':
                case 'neq':
                    $token = '!=';
                    break;

                case 'lt':
                    $token = '<';
                    break;

                case 'le':
                case 'lte':
                    $token = '<=';
                    break;

                case 'gt':
                    $token = '>';
                    break;

                case 'ge':
                case 'gte':
                    $token = '>=';
                    break;

                case 'and':
                    $token = '&&';
                    break;

                case 'or':
                    $token = '||';
                    break;

                case 'not':
                    $token = '!';
                    break;

                case 'mod':
                    $token = '%';
                    break;

                default:
                    if ($token[0] == '$') {
                        $token = $this->getVal(substr($token, 1));
                    }
                    break;
            }
        }

        if ($elseif) {
            return '<?php elseif (' . implode(' ', $tokens) . '): ?>';
        } else {
            return '<?php if (' . implode(' ', $tokens) . '): ?>';
        }
    }

    /**
     * 处理foreach标签
     *
     * @access  public
     * @param   string $tag_args
     *
     * @return  string
     */
    public function compileForeachStart($tag_args)
    {
        $attrs = $this->getPara($tag_args, 0);
        $from = $attrs['from'];

        $item = $this->getVal($attrs['item']);

        if (!empty($attrs['key'])) {
            $key = $attrs['key'];
            $key_part = $this->getVal($key) . ' => ';
        } else {
            $key = null;
            $key_part = '';
        }

        if (!empty($attrs['name'])) {
            $name = $attrs['name'];
        } else {
            $name = null;
        }

        $output = '<?php ';
        $output .= "\$_from = $from; if (!is_array(\$_from) && !is_object(\$_from)) { settype(\$_from, 'array'); }";
        if (!empty($name)) {
            $foreach_props = "\$this->_foreach['$name']";
            $output .= "{$foreach_props} = array('total' => count(\$_from), 'iteration' => 0);\n";
            $output .= "if ({$foreach_props}['total'] > 0):\n";
            $output .= "    foreach (\$_from as $key_part$item):\n";
            $output .= "        {$foreach_props}['iteration']++;\n";
        } else {
            $output .= "if (count(\$_from)):\n";
            $output .= "    foreach (\$_from as $key_part$item):\n";
        }

        return $output . '?>';
    }

    /**
     * 处理smarty开头的预定义变量
     *
     * @param   array $indexes
     *
     * @return  string
     */
    public function compileSmartyRef(&$indexes)
    {
        /* Extract the reference name. */
        $_ref = $indexes[0];

        $compiled_ref = '';
        switch ($_ref) {
            case 'now':
                $compiled_ref = 'time()';
                break;

            case 'foreach':
                array_shift($indexes);
                $_var = $indexes[0];
                $_propname = $indexes[1];
                switch ($_propname) {
                    case 'index':
                        array_shift($indexes);
                        $compiled_ref = "(\$this->_foreach['$_var']['iteration'] - 1)";
                        break;

                    case 'first':
                        array_shift($indexes);
                        $compiled_ref = "(\$this->_foreach['$_var']['iteration'] <= 1)";
                        break;

                    case 'last':
                        array_shift($indexes);
                        $compiled_ref = "(\$this->_foreach['$_var']['iteration'] == "
                            . "\$this->_foreach['$_var']['total'])";
                        break;

                    case 'show':
                        array_shift($indexes);
                        $compiled_ref = "(\$this->_foreach['$_var']['total'] > 0)";
                        break;

                    default:
                        $compiled_ref = "\$this->_foreach['$_var']";
                        break;
                }
                break;

            case 'get':
                $compiled_ref = '$_GET';
                break;

            case 'post':
                $compiled_ref = '$_POST';
                break;

            case 'cookies':
                $compiled_ref = '$_COOKIE';
                break;

            case 'env':
                $compiled_ref = '$_ENV';
                break;

            case 'server':
                $compiled_ref = '$_SERVER';
                break;

            case 'request':
                $compiled_ref = '$_REQUEST';
                break;

            case 'session':
                $compiled_ref = '$_SESSION';
                break;

            default:
                break;
        }
        array_shift($indexes);

        return $compiled_ref;
    }

    /**
     * smartyInsertScripts
     *
     * @param $args
     * @return string
     */
    public function smartyInsertScripts($args)
    {
        static $scripts = array();

        $arr = explode(',', str_replace(' ', '', $args['files']));

        $str = '';
        foreach ($arr as $val) {
            if (in_array($val, $scripts) == false) {
                $scripts[] = $val;
                if ($val{0} == '.') {
                    $str .= '<script type="text/javascript" src="/' . $val . '"></script>';
                } else {
                    //$str .= '<script type="text/javascript" src="/js/' . $val . '"></script>';
                }
            }
        }

        return $str;
    }

    /**
     * strTrim
     *
     * @param $str
     * @return array
     */
    private function strTrim($str)
    {
        /* 处理'a=b c=d k = f '类字符串，返回数组 */
        while (strpos($str, '= ') != 0) {
            $str = str_replace('= ', '=', $str);
        }
        while (strpos($str, ' =') != 0) {
            $str = str_replace(' =', '=', $str);
        }

        return explode(' ', trim($str));
    }

    /**
     * evalFuc
     *
     * @param $content
     * @return string
     */
    public function evalFuc($content)
    {
        ob_start();
        eval('?>' . trim($content));
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * include文件
     *
     * @param $filename
     * @return string
     */
    private function requireFile($filename)
    {
        ob_start();
        include("$filename");
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * htmlOptions
     *
     * @param $arr
     * @return string
     */
    public function htmlOptions($arr)
    {
        $selected = $arr['selected'];
        $options = array();
        if ($arr['options']) {
            $options = (array)$arr['options'];
        } elseif ($arr['output']) {
            if ($arr['values']) {
                foreach ($arr['output'] as $key => $val) {
                    $options["{$arr['values'][$key]}"] = $val;
                }
            } else {
                $options = array_values((array)$arr['output']);
            }
        }
        $out = '';
        if ($options) {
            foreach ($options as $key => $val) {
                $out .= $key == $selected ? "<option value=\"$key\" selected>$val</option>"
                    : "<option value=\"$key\">$val</option>";
            }
        }

        return $out;
    }

    /**
     * htmlSelectDate
     *
     * @param $arr
     * @return string
     */
    public function htmlSelectDate($arr)
    {
        $year = $month = $day = '';
        $pre = $arr['prefix'];
        if (isset($arr['time'])) {
            if (intval($arr['time']) > 10000) {
                $arr['time'] = gmdate('Y-m-d', $arr['time'] + 8 * 3600);
            }
            $t = explode('-', $arr['time']);
            $year = strval($t[0]);
            $month = strval($t[1]);
            $day = strval($t[2]);
        }
        $now = gmdate('Y', time());
        if (isset($arr['start_year'])) {
            if (abs($arr['start_year']) == $arr['start_year']) {
                $startyear = $arr['start_year'];
            } else {
                $startyear = $arr['start_year'] + $now;
            }
        } else {
            $startyear = $now - 3;
        }

        if (isset($arr['end_year'])) {
            if (strlen(abs($arr['end_year'])) == strlen($arr['end_year'])) {
                $endyear = $arr['end_year'];
            } else {
                $endyear = $arr['end_year'] + $now;
            }
        } else {
            $endyear = $now + 3;
        }

        $out = "<select name=\"{$pre}Year\">";
        for ($i = $startyear; $i <= $endyear; $i++) {
            $out .= $i == $year ? "<option value=\"$i\" selected>$i</option>" : "<option value=\"$i\">$i</option>";
        }
        if ($arr['display_months'] != 'false') {
            $out .= "</select>&nbsp;<select name=\"{$pre}Month\">";
            for ($i = 1; $i <= 12; $i++) {
                $out .= $i == $month
                    ? "<option value=\"$i\" selected>"
                    . str_pad($i, 2, '0', STR_PAD_LEFT)
                    . "</option>"
                    : "<option value=\"$i\">" . str_pad($i, 2, '0', STR_PAD_LEFT)
                    . "</option>";
            }
        }
        if ($arr['display_days'] != 'false') {
            $out .= "</select>&nbsp;<select name=\"{$pre}Day\">";
            for ($i = 1; $i <= 31; $i++) {
                $out .= $i == $day ? "<option value=\"$i\" selected>"
                    . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>"
                    : "<option value=\"$i\">" . str_pad($i, 2, '0', STR_PAD_LEFT)
                    . "</option>";
            }
        }

        return $out . '</select>';
    }

    /**
     * htmlRadios
     *
     * @param $arr
     * @return string
     */
    public function htmlRadios($arr)
    {
        $name = $arr['name'];
        $checked = $arr['checked'];
        $options = $arr['options'];

        $out = '';
        foreach ($options as $key => $val) {
            $out .= $key == $checked ? "<input type=\"radio\" name=\"$name\" value=\"$key\" checked>&nbsp;{$val}&nbsp;"
                : "<input type=\"radio\" name=\"$name\" value=\"$key\">&nbsp;{$val}&nbsp;";
        }

        return $out;
    }

    /**
     * htmlSelectTime
     *
     * @param $arr
     * @return string
     */
    public function htmlSelectTime($arr)
    {
        $hour = $minute = $second = '';
        $pre = $arr['prefix'];
        if (isset($arr['time'])) {
            $arr['time'] = gmdate('H-i-s', $arr['time'] + 8 * 3600);
            $t = explode('-', $arr['time']);
            $hour = strval($t[0]);
            $minute = strval($t[1]);
            $second = strval($t[2]);
        }
        $out = '';
        if (!isset($arr['display_hours'])) {
            $out .= "<select name=\"{$pre}Hour\">";
            for ($i = 0; $i <= 23; $i++) {
                $out .= $i == $hour ? "<option value=\"$i\" selected>"
                    . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>"
                    : "<option value=\"$i\">" . str_pad($i, 2, '0', STR_PAD_LEFT)
                    . "</option>";
            }

            $out .= "</select>&nbsp;";
        }
        if (!isset($arr['display_minutes'])) {
            $out .= "<select name=\"{$pre}Minute\">";
            for ($i = 0; $i <= 59; $i++) {
                $out .= $i == $minute ? "<option value=\"$i\" selected>"
                    . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>"
                    : "<option value=\"$i\">" . str_pad($i, 2, '0', STR_PAD_LEFT)
                    . "</option>";
            }

            $out .= "</select>&nbsp;";
        }
        if (!isset($arr['display_seconds'])) {
            $out .= "<select name=\"{$pre}Second\">";
            for ($i = 0; $i <= 59; $i++) {
                $out .= $i == $second ? "<option value=\"$i\" selected>"
                    . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>"
                    : "<option value=\"$i\">$i</option>";
            }

            $out .= "</select>&nbsp;";
        }

        return $out;
    }

    /**
     * cycle
     *
     * @param $arr
     */
    public function cycle($arr)
    {
        static $k, $old;

        $value = explode(',', $arr['values']);
        if ($old != $value) {
            $old = $value;
            $k = 0;
        } else {
            $k++;
            if (!isset($old[$k])) {
                $k = 0;
            }
        }

        echo $old[$k];
    }

    /**
     * makeArray
     *
     * @param $arr
     * @return string
     */
    private function makeArray($arr)
    {
        $out = '';
        foreach ($arr as $key => $val) {
            if ($val{0} == '$') {
                $out .= $out ? ",'$key'=>$val" : "array('$key'=>$val";
            } else {
                $out .= $out ? ",'$key'=>'$val'" : "array('$key'=>'$val'";
            }
        }

        return $out . ')';
    }

    /**
     * smartyCreatePages
     *
     * @param $params
     * @return string
     */
    public function smartyCreatePages($params)
    {

        extract($params);

        if (empty($page)) {
            $page = 1;
        }

        if (!empty($count)) {
            $str = "<option value='1'>1</option>";
            $min = min($count - 1, $page + 3);
            for ($i = $page - 3; $i <= $min; $i++) {
                if ($i < 2) {
                    continue;
                }
                $str .= "<option value='$i'";
                $str .= $page == $i ? " selected='true'" : '';
                $str .= ">$i</option>";
            }
            if ($count > 1) {
                $str .= "<option value='$count'";
                $str .= $page == $count ? " selected='true'" : '';
                $str .= ">$count</option>";
            }
        } else {
            $str = '';
        }

        return $str;
    }
}
