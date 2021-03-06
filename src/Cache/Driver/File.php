<?php

namespace Mll\Cache\Driver;

use Mll\Cache\Base;
use Mll\Cache\Cache;
use Mll\Cache\ICache;

/**
 * 文件缓存
 *
 * @package Mll\Cache\Driver
 * @author wang zhou <zhouwang@mll.com>
 * @since 1.0
 */
class File extends Base implements ICache
{
    protected $options = [
        'expire'        => 0,
        'cache_subdir'  => false,
        'prefix'        => '',
        'path'          => '',
        'data_compress' => false,
    ];

    /**
     * 架构函数
     * @param array $options
     */
    public function __construct($options = [])
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        if (substr($this->options['path'], -1) != DS) {
            $this->options['path'] .= DS;
        }
        $this->options['path'] = ROOT_PATH . $this->options['path'];
        $this->checkCacheDir();
    }

    public function init($driver, $cacheName = '')
    {
        return Cache::init($driver, $cacheName);
    }

    /**
     * 初始化检查
     *
     * @access private
     *
     * @return boolean
     */
    private function checkCacheDir()
    {
        // 创建项目缓存目录
        if (!is_dir($this->options['path'])) {
            if (mkdir($this->options['path'], 0755, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 取得变量的存储文件名
     *
     * @access protected
     * @param string $name 缓存变量名
     *
     * @return string
     */
    protected function getCacheKey($name)
    {
        $name = md5($name);
        if ($this->options['cache_subdir']) {
            // 使用子目录
            $name = substr($name, 0, 2) . DS . substr($name, 2);
        }
        if ($this->options['prefix']) {
            $name = $this->options['prefix'] . DS . $name;
        }
        $filename = $this->options['path'] . $name . '.php';
        $dir      = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $filename;
    }

    /**
     * 判断缓存是否存在
     *
     * @access public
     * @param string $name 缓存变量名
     *
     * @return bool
     */
    public function has($name)
    {
        return $this->get($name) ? true : false;
    }

    /**
     * 读取缓存
     *
     * @access public
     * @param string $name 缓存变量名
     * @param mixed  $default 默认值
     *
     * @return mixed
     */
    public function get($name, $default = false)
    {
        $filename = $this->getCacheKey($name);
        if (!is_file($filename)) {
            return $default;
        }
        $content = file_get_contents($filename);
        if (false !== $content) {
            $expire = (int) substr($content, 8, 12);
            if (0 != $expire && $_SERVER['REQUEST_TIME'] > filemtime($filename) + $expire) {
                //缓存过期删除缓存文件
                $this->unlink($filename);
                return $default;
            }
            $content = substr($content, 20, -3);
            if ($this->options['data_compress'] && function_exists('gzcompress')) {
                //启用数据压缩
                $content = gzuncompress($content);
            }
            $content = unserialize($content);
            return $content;
        } else {
            return $default;
        }
    }

    /**
     * 写入缓存
     *
     * @access public
     * @param string    $name 缓存变量名
     * @param mixed     $value  存储数据
     * @param int       $expire  有效时间 0为永久
     *
     * @return boolean
     */
    public function set($name, $value, $expire = null)
    {
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        $filename = $this->getCacheKey($name);
        if ($this->tag && !is_file($filename)) {
            $first = true;
        }
        $data = serialize($value);
        if ($this->options['data_compress'] && function_exists('gzcompress')) {
            //数据压缩
            $data = gzcompress($data, 3);
        }
        $data   = "<?php\n//" . sprintf('%012d', $expire) . $data . "\n?>";
        $result = file_put_contents($filename, $data, LOCK_EX);
        if ($result) {
            isset($first) && $this->setTagItem($filename);
            clearstatcache();
            return true;
        } else {
            return false;
        }
    }

    /**
     * 自增缓存（针对数值缓存）
     *
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     *
     * @return false|int
     */
    public function inc($name, $step = 1)
    {
        if ($this->has($name)) {
            $value = $this->get($name) + $step;
        } else {
            $value = $step;
        }
        return $this->set($name, $value, 0) ? $value : false;
    }

    /**
     * 自减缓存（针对数值缓存）
     *
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     *
     * @return false|int
     */
    public function dec($name, $step = 1)
    {
        if ($this->has($name)) {
            $value = $this->get($name) - $step;
        } else {
            $value = $step;
        }
        return $this->set($name, $value, 0) ? $value : false;
    }

    /**
     * 删除缓存
     *
     * @access public
     * @param string $name 缓存变量名
     *
     * @return boolean
     */
    public function rm($name)
    {
        return $this->unlink($this->getCacheKey($name));
    }

    /**
     * 清除缓存
     *
     * @access public
     * @param string $tag 标签名
     *
     * @return boolean
     */
    public function clear($tag = null)
    {
        if ($tag) {
            // 指定标签清除
            $keys = $this->getTagItem($tag);
            foreach ($keys as $key) {
                $this->unlink($key);
            }
            $this->rm('tag_' . md5($tag));
            return true;
        }
        $fileLsit = (array) glob($this->options['path'] . '*');
        foreach ($fileLsit as $path) {
            is_file($path) && unlink($path);
        }
        return true;
    }

    /**
     * 判断文件是否存在后，删除
     *
     * @param $path
     * @return bool
     * @author byron sampson <xiaobo.sun@qq.com>
     *
     * @return boolean
     */
    private function unlink($path)
    {
        return is_file($path) && unlink($path);
    }
}
