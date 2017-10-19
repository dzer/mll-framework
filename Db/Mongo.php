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
     * @var \MongoDB\Driver\Manager
     */
    public $mongo; //mongo对象
    /**
     * @var \MongoDb
     */
    private $db; //db mongodb对象数据库
    /**
     * @var \MongoCollection
     */
    private $collection; //集合，相当于数据表

    private $query;

    private $config = [
        'host' => '192.168.0.155:27017', //服务器地址
        'database' => 'meilele_log_fenxi', //数据库名称
        'username' => 'logfenxi', //数据库用户名
        'password' => '9C1Xh(86%E7DFe', //数据库密码
        'options' => [
            //'connectTimeoutMS' => 2000,
            //'socketTimeoutMSG' => 3000,
            'readPreference' => \MongoDB\Driver\ReadPreference::RP_PRIMARY_PREFERRED
        ]
    ];

    public function __construct($config = array())
    {
        if (empty($config)) {
            $this->config = array_merge($this->config, Mll::app()->config->get('db.mongo', []));
        } else {
            $this->config = $config;
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
            $host = 'mongodb://' . ($config['username'] ? "{$config['username']}" : '')
                . ($config['password'] ? ":{$config['password']}@" : '')
                . $config['host'] . '/' . ($config['database'] ? "{$config['database']}" : '');
            $this->mongo = new \MongoDB\Driver\Manager($host, $options);
            $this->db = $config['database'];
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
        $this->db = $dbName;
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
        $this->collection = $collection;
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
    public function batchInsert($data)
    {
        if (empty($data)) {
            return false;
        }
        $bulk = new \MongoDB\Driver\BulkWrite();
        foreach ($data as $v) {
            if (isset($v)) {
                try {
                    $bulk->insert($v);
                } catch (\Exception $e) {
                    Mll::app()->log->error('mongo插入数据错误：' . $e->getMessage());
                    continue;
                }
            }
        }
        $writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 5000);
        $result = $this->mongo->executeBulkWrite("{$this->db}.{$this->collection}", $bulk, $writeConcern);
        return $result->getInsertedCount();
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
     * @param array $option 参数 array('multi' => false, 'upsert' => false)
     * @return bool
     */
    public function update($query, $data, $option = array())
    {
        if (empty($data) || empty($query)) {
            return false;
        }
        $bulk = new \MongoDB\Driver\BulkWrite();
        $bulk->update(
            $query,
            $data,
            $option
        );
        $result = $this->mongo->executeBulkWrite("{$this->db}.{$this->collection}", $bulk);
        return $result->getMatchedCount() + $result->getUpsertedCount();
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
        $options = [
            'projection' => $fields,
            'fields' => $fields
        ];

        $query = new \MongoDB\Driver\Query($query, $options);
        $cursor = $this->mongo->executeQuery("{$this->db}.{$this->collection}", $query);
        return $cursor->toArray();
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
        $options = [
            'projection' => $fields,
            'sort' => $sort,
            'skip' => $skip,
            'limit' => $limit,
            'fields' => $fields
        ];

        $query = new \MongoDB\Driver\Query($query, $options);
        $readPreference = new \MongoDB\Driver\ReadPreference(\MongoDB\Driver\ReadPreference::RP_PRIMARY_PREFERRED);
        $cursor = $this->mongo->executeQuery("{$this->db}.{$this->collection}", $query, $readPreference);
        return $cursor->toArray();
    }

    /**
     * 分析查询语句
     *
     * @param array $command 命令
     * @return array
     */
    public function explain($command = array())
    {
        $command['explain'] = true;
        $cursor = $this->mongo->executeCommand("{$this->db}", new \MongoDB\Driver\Command($command));
        return $cursor->toArray();
    }

    /**
     * 数据统计
     *
     * @return int
     */
    public function count($query, $options = array())
    {
        $commands = [
            'count' => "{$this->collection}",
            'query' => $query,
        ];
        if (!empty($options)) {
            $commands = array_merge($commands, $options);
        }
        $command = new \MongoDB\Driver\Command($commands);
        $cursor = $this->mongo->executeCommand("{$this->db}", $command);
        return !empty($cursor) ? $cursor->toArray()[0]->n : false;
    }

    /**
     * 执行命令
     *
     * @param array $command 命令
     * @return array|object
     */
    public function executeCommand($command = array())
    {
        $cursor = $this->mongo->executeCommand("{$this->db}", new \MongoDB\Driver\Command($command));
        return $cursor->toArray();
    }

    /**
     * 错误信息
     *
     * @return array
     */
    public function error($query = array())
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
        $this->mongo = null;
    }
}