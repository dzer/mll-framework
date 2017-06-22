<?php

namespace Mll\Cache;

/**
 * 缓存基础类.
 */
abstract class Base
{
    protected $options = [];
    protected $tag;

    /**
     * 获取实际的缓存标识.
     *
     * @param string $name 缓存名
     *
     * @return string
     */
    protected function getCacheKey($name)
    {
        return $this->options['prefix'].$name;
    }

    /**
     * 读取缓存并删除.
     *
     * @param string $name 缓存变量名
     *
     * @return mixed
     */
    public function pull($name)
    {
        $result = $this->get($name, false);
        if ($result) {
            $this->rm($name);

            return $result;
        } else {
            return null;
        }
    }

    /**
     * 缓存标签.
     *
     * @param string       $name    标签名
     * @param string|array $keys    缓存标识
     * @param bool         $overlay 是否覆盖
     *
     * @return $this
     */
    public function tag($name, $keys = null, $overlay = false)
    {
        if (is_null($keys)) {
            $this->tag = $name;
        } else {
            $key = 'tag_'.md5($name);
            if (is_string($keys)) {
                $keys = explode(',', $keys);
            }
            $keys = array_map([$this, 'getCacheKey'], $keys);
            if ($overlay) {
                $value = $keys;
            } else {
                $value = array_unique(array_merge($this->getTagItem($name), $keys));
            }
            $this->set($key, implode(',', $value));
        }

        return $this;
    }

    /**
     * 更新标签.
     *
     * @param string $name 缓存标识
     */
    protected function setTagItem($name)
    {
        if ($this->tag) {
            $key = 'tag_'.md5($this->tag);
            $this->tag = null;
            if ($this->has($key)) {
                $value = $this->get($key);
                $value .= ','.$name;
            } else {
                $value = $name;
            }
            $this->set($key, $value);
        }
    }

    /**
     * 获取标签包含的缓存标识.
     *
     * @param string $tag 缓存标签
     *
     * @return array
     */
    protected function getTagItem($tag)
    {
        $key = 'tag_'.md5($tag);
        $value = $this->get($key);
        if ($value) {
            return explode(',', $value);
        } else {
            return [];
        }
    }
}
