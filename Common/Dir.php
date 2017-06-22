<?php

namespace Mll\Common;

/**
 * 目录工具类
 *
 * @package Mll\Common
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Dir
{
    /**
     * 递归创建目录.
     *
     * @param string $dir 目录路径
     * @param int $mode 目录权限
     *
     * @return bool
     */
    public static function make($dir, $mode = 0755)
    {
        if (is_dir($dir) || mkdir($dir, $mode, true)) {
            return true;
        }
        if (!self::make(dirname($dir), $mode)) {
            return false;
        }

        return mkdir($dir, $mode);
    }

    /**
     * 递归获取目录下的文件.
     *
     * @param string $dir 目录路径
     * @param string $filter 过滤目录，正则表达式
     * @param array $result 文件列表
     * @param bool $deep 是否递归
     *
     * @return mixed
     */
    public static function tree($dir, $filter = '', &$result = array(), $deep = false)
    {
        try {
            $files = new \DirectoryIterator($dir);
            foreach ($files as $file) {
                if ($file->isDot()) {
                    continue;
                }
                $filename = $file->getFilename();
                if ($file->isDir()) {
                    if ($deep) {
                        self::tree($dir . DS . $filename, $filter, $result, $deep);
                    }
                } else {
                    if (!empty($filter) && !preg_match($filter, $filename)) {
                        continue;
                    }
                    if ($deep) {
                        $result[$dir] = $filename;
                    } else {
                        $result[] = $dir . DS . $filename;
                    }
                }
            }

            return $result;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 递归删除目录.
     *
     * @param string $dir 目录路径
     * @param string $filter 过滤目录，正则表达式
     *
     * @return bool
     */
    public static function del($dir, $filter = '')
    {
        $files = new \DirectoryIterator($dir);
        foreach ($files as $file) {
            if ($file->isDot()) {
                continue;
            }
            $filename = $file->getFilename();
            if (!empty($filter) && !preg_match($filter, $filename)) {
                continue;
            }
            if ($file->isDir()) {
                self::del($dir . DS . $filename);
            } else {
                unlink($dir . DS . $filename);
            }
        }

        return rmdir($dir);
    }
}
