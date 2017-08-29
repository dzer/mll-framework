<?php

namespace Mll\Db;

use Mll\Mll;
use MongoDB\Driver;

/**
 * Mongo类.
 *
 * @author Xu Dong <d20053140@gmail.com>
 *
 * @since 1.0
 */
class Mongo
{
    /**
     * @var Driver\Server
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

    //private $query;

    private $config = [
        'host' => '192.168.0.104:27017', //服务器地址
        'database' => 'system_log', //数据库名称
        'username' => '', //数据库用户名
        'password' => '', //数据库密码
        'options' => [
            'connectTimeoutMS' => 2000,
            'socketTimeoutMSG' => 3000,
            'readPreference' => Driver\ReadPreference::RP_SECONDARY_PREFERRED,
        ],
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
     * connect.
     *
     * @param array $config 配置
     *
     * @return object
     */
    public function connect($config = array())
    {
        if (empty($this->mongo)) {
            $options = array();
            if (!empty($config['options'])) {
                $options = $config['options'];
            }
            $host = 'mongodb://' . ($config['username'] ? "{$config['username']}" : '')
                . ($config['password'] ? ":{$config['password']}@" : '')
                . $config['host'] . '/' . ($config['database'] ? "{$config['database']}" : '');
            $this->mongo = new Driver\Manager($host, $options);
            $this->db = $config['database'];
        }

        return $this->mongo;
    }

    /**
     * 选择db.
     *
     * @param $dbName
     *
     * @return $this
     */
    public function setDBName($dbName)
    {
        $this->db = $dbName;

        return $this;
    }

    /**
     * 选择一个集合，相当于选择一个数据表.
     *
     * @param $collection
     *
     * @return $this
     * @return \MongoCollection
     */
    public function selectCollection($collection)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * 新增数据.
     *
     * @param array $data 要新增的数据 例如：array('title' => '1000', 'username' => 'xcxx')
     * @param integer $timeout 超时时间
     *
     * @return int
     */
    public function insert($data, $timeout = 5000)
    {
        if (empty($data)) {
            return false;
        }
        $bulk = new Driver\BulkWrite();
        $bulk->insert($data);

        $writeConcern = new Driver\WriteConcern(Driver\WriteConcern::MAJORITY, $timeout);
        $result = $this->mongo->executeBulkWrite("{$this->db}.{$this->collection}", $bulk, $writeConcern);

        return $result->getInsertedCount();
    }

    /**
     * 批量新增数据.
     *
     * @param array $data 需要新增的数据 例如：array(0=>array('title' => '1000', 'username' => 'xcxx'))
     * @param integer $timeout 超时时间
     *
     * @return int
     */
    public function batchInsert($data, $timeout = 5000)
    {
        if (empty($data)) {
            return false;
        }
        $bulk = new Driver\BulkWrite();
        foreach ($data as $v) {
            $bulk->insert($v);
        }
        $writeConcern = new Driver\WriteConcern(Driver\WriteConcern::MAJORITY, $timeout);
        $result = $this->mongo->executeBulkWrite("{$this->db}.{$this->collection}", $bulk, $writeConcern);

        return $result->getInsertedCount();
    }

    /**
     * 保存数据，如果已经存在在库中，则更新，不存在，则新增.
     *
     * @param array $data 需要新增的数据 例如：array(0=>array('title' => '1000', 'username' => 'xcxx'))
     *
     * @return int
     */
    public function save($data)
    {
        if (empty($data)) {
            return false;
        }
        $bulk = new Driver\BulkWrite();
        $bulk->update(
            $data,
            $data,
            ['multi' => false, 'upsert' => true]
        );
        $writeConcern = new Driver\WriteConcern(Driver\WriteConcern::MAJORITY, 5000);
        $result = $this->mongo->executeBulkWrite("{$this->db}.{$this->collection}", $bulk, $writeConcern);

        return $result->getModifiedCount();
    }

    /**
     * 根据条件移除.
     *
     * @param array $query 条件 例如：array(('title' => '1000'))
     * @param bool $limit 是否只删除第一个匹配的文档
     *
     * @return int
     */
    public function remove($query, $limit = false)
    {
        if (empty($query)) {
            return false;
        }
        $bulk = new Driver\BulkWrite();
        $bulk->delete($query, ['limit' => $limit]);
        $writeConcern = new Driver\WriteConcern(Driver\WriteConcern::MAJORITY, 5000);
        $result = $this->mongo->executeBulkWrite("{$this->db}.{$this->collection}", $bulk, $writeConcern);

        return $result->getDeletedCount();
    }

    /**
     * 根据条件更新数据.
     *
     * @param array $query 条件 例如：array(('title' => '1000'))
     * @param array $data 需要更新的数据 例如：array(0=>array('title' => '1000', 'username' => 'xcxx'))
     *
     * @return int
     */
    public function update($query, $data)
    {
        if (empty($data)) {
            return false;
        }
        $bulk = new Driver\BulkWrite();
        $bulk->update(
            $query,
            $data,
            ['multi' => true, 'upsert' => false]
        );
        $writeConcern = new Driver\WriteConcern(Driver\WriteConcern::MAJORITY, 5000);
        $result = $this->mongo->executeBulkWrite("{$this->db}.{$this->collection}", $bulk, $writeConcern);

        return $result->getModifiedCount();
    }

    /**
     * 根据条件查找一条数据.
     *
     * @param array $query 条件 例如：array(('title' => '1000'))
     * @param array $fields 参数
     *
     * @return array|null
     */
    public function findOne($query, $fields = array())
    {
        $options = [
            'projection' => $fields,
            'fields' => $fields,
        ];

        $query = new Driver\Query($query, $options);
        $cursor = $this->mongo->executeQuery("{$this->db}.{$this->collection}", $query);

        return $cursor->toArray();
    }

    /**
     * 根据条件查找多条数据.
     *
     * @param array $query 查询条件
     * @param array $sort 排序条件 array('age' => -1, 'username' => 1)
     * @param int $skip 跳过条数
     * @param int $limit 返回条数
     * @param array $fields 返回的字段
     *
     * @return array|\MongoCursor
     */
    public function find($query = array(), $sort = array(), $skip = 0, $limit = 0, $fields = array())
    {
        $options = [
            'projection' => $fields,
            'sort' => $sort,
            'skip' => $skip,
            'limit' => $limit,
            'fields' => $fields,
        ];

        $query = new Driver\Query($query, $options);
        $readPreference = new Driver\ReadPreference(Driver\ReadPreference::RP_SECONDARY_PREFERRED);
        $cursor = $this->mongo->executeQuery("{$this->db}.{$this->collection}", $query, $readPreference);

        return $cursor->toArray();
    }

    /**
     * 分析查询语句.
     *
     * @param $query
     *
     * @return array
     */
    public function explain($query)
    {
        return $this->collection->find($query)->explain();
    }

    /**
     * 数据统计
     *
     * @param array $query 命令
     * @return int
     */
    public function count($query = array())
    {
        $commands = [
            'count' => "{$this->collection}",
            'query' => $query,
        ];
        $command = new Driver\Command($commands);
        $cursor = $this->mongo->executeCommand("{$this->db}", $command);

        return !empty($cursor) ? $cursor->toArray()[0]->n : false;
    }

    /**
     * 执行命令.
     *
     * @param array $command 命令
     * @return array|object
     */
    public function executeCommand($command = array())
    {
        $cursor = $this->mongo->executeCommand("{$this->db}", new Driver\Command($command));

        return $cursor->toArray();
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

    /**
     * 析构
     */
    public function __destruct()
    {
        $this->mongo = null;
    }
}
