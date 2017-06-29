<?php

namespace Mll\Db;

use Mll\Mll;

/**
 * Mongo类
 *
 * @package Mll\Exception
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Mongo
{
    /**
     * @var \Mongo
     */
    private $mongo; //mongo对象
    /**
     * @var \MongoDb
     */
    private $db; //db mongodb对象数据库
    /**
     * @var \MongoCollection
     */
    private $collection; //集合，相当于数据表

    private $config = [
        'dsn' => 'mongodb://192.168.2.214:27017', //服务器地址
        'option' => [
            'connect' => true, //参数
            'db_name' => 'system_log', //数据库名称
            'username' => '', //数据库用户名
            'password' => '', //数据库密码
        ]
    ];

    public function __construct($config = array())
    {
        if (empty($config)) {
            $this->config = array_merge($this->config, Mll::app()->config->get('db.mongo', []));
        }
        $this->connect($this->config);
        return $this;
    }

    /**
     * connect
     *
     * @param array $config 配置
     * @return object
     */
    public function connect($config = array())
    {
        if (empty($this->mongo)) {
            $options = array();
            if (!empty($config['options'])) {
                $options = $config['options'];
            }
            $this->mongo = new \MongoClient($config['dsn'], $options);
        }
        return $this->mongo;
    }

    /**
     * 选择db
     *
     * @param $dbName
     * @return $this
     */
    public function setDBName($dbName)
    {
        $this->db = $this->mongo->selectDB($dbName);
        return $this;
    }

    /**
     * 选择一个集合，相当于选择一个数据表
     *
     * @param $collection
     * @return $this
     * @return \MongoCollection
     */
    public function selectCollection($collection)
    {
        $this->collection = $this->db->selectCollection($collection);
        return $this;
    }

    /**
     * 新增数据
     *
     * @param array $data 要新增的数据 例如：array('title' => '1000', 'username' => 'xcxx')
     * @param array $option 参数
     * @return array|bool
     */
    public function insert($data, $option = array())
    {
        return $this->collection->insert($data, $option);
    }

    /**
     * 批量新增数据
     *
     * @param array $data 需要新增的数据 例如：array(0=>array('title' => '1000', 'username' => 'xcxx'))
     * @param array $option 参数
     * @return mixed
     */
    public function batchInsert($data, $option = array())
    {
        return $this->collection->batchInsert($data, $option);
    }

    /**
     * 保存数据，如果已经存在在库中，则更新，不存在，则新增
     *
     * @param array $data 需要新增的数据 例如：array(0=>array('title' => '1000', 'username' => 'xcxx'))
     * @param array $option 参数
     * @return array|bool
     */
    public function save($data, $option = array())
    {
        return $this->collection->save($data, $option);
    }

    /**
     * 根据条件移除
     *
     * @param array $query 条件 例如：array(('title' => '1000'))
     * @param array $option 参数
     * @return array|bool
     */
    public function remove($query, $option = array())
    {
        return $this->collection->remove($query, $option);
    }

    /**
     * 根据条件更新数据
     *
     * @param array $query 条件 例如：array(('title' => '1000'))
     * @param array $data 需要更新的数据 例如：array(0=>array('title' => '1000', 'username' => 'xcxx'))
     * @param array $option 参数
     * @return bool
     */
    public function update($query, $data, $option = array())
    {
        return $this->collection->update($query, $data, $option);
    }

    /**
     * 根据条件查找一条数据
     *
     * @param array $query 条件 例如：array(('title' => '1000'))
     * @param array $fields 参数
     * @return array|null
     */
    public function findOne($query, $fields = array())
    {
        return $this->collection->findOne($query, $fields);
    }

    /**
     * 根据条件查找多条数据
     *
     * @param array $query 查询条件
     * @param array $sort 排序条件 array('age' => -1, 'username' => 1)
     * @param int $skip 跳过条数
     * @param int $limit 返回条数
     * @param array $fields 返回的字段
     * @return array|\MongoCursor
     */
    public function find($query = array(), $sort = array(), $skip = 0, $limit = 0, $fields = array())
    {
        $cursor = $this->collection->find($query, $fields);
        $count = $cursor->count();
        if (empty($count)) {
            return array();
        }
        if ($sort) $cursor->sort($sort);
        if ($skip) $cursor->skip($skip);
        if ($limit) $cursor->limit($limit);
        return iterator_to_array($cursor);
    }

    /**
     * 分析查询语句
     *
     * @param $query
     * @return array
     */
    public function explain($query)
    {
        return $this->collection->find($query)->explain();
    }

    /**
     * 数据统计
     *
     * @return int
     */
    public function count()
    {
        return $this->collection->count();
    }

    /**
     * 错误信息
     *
     * @return array
     */
    public function error()
    {
        return $this->db->lastError();
    }

    /**
     * 获取集合对象
     *
     * @return \MongoCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * 获取DB对象
     *
     * @return \MongoDb
     */
    public function getDb()
    {
        return $this->db;
    }

    public function __destruct()
    {
        $connections = $this->mongo->getConnections();
        foreach ((array)$connections as $con) {
            $this->mongo->close($con['hash']);
        }
    }
}