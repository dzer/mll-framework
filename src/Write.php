<?php

namespace Mll;

use Mll\Common\Common;
use Mll\Common\ErpTableIdHelper;
use Mll\Common\ReturnMsg;
use Mll\Common\Rule;

/**
 * 规则调用类
 *
 * @package Mll\Cache
 * @author wang zhou <zhouwang@mll.com>
 * @since 1.0
 */
class Write
{
	var $transactionNum    = ''; # 运行中事务号数

	public static $transaction_table = 'ecsi_transaction_table'; # 设定默认的规则接口支持表
	public static $out_time          = 0; # 超时时间
	public static $rule_out_time     = 10; # 规则超时时间 半个小时 1800
	public static $RuleMaxHandle     = 500; # 规则最大支持的数据吞吐条数
    /**
     * 获取类名
     *
     * @return string
     */
	function getClassName() {
		return __CLASS__;
	}
	
	public function __construct(){
        //
	}

    /**
     * checkTranscation
     *
     * @return bool
     */
	function checkTranscation() {
		if(empty($this) 
			|| !method_exists($this, 'getClassName') 
			|| $this->getClassName() != __CLASS__
		) {
			return false;
		}

		return true;
	}

    /**
     * 开启事务支持
     *
     * @return array
     */
	function tStart() {
		if(!self::checkTranscation()) {
            $msg = '请使用实例开启事务!';
            return ReturnMsg::err($msg);
        }
        $serviceName = '';
		$info = Rule::callUrl(RULE_T_START, self::$out_time, array(
			'serviceName'        => self::$transaction_table,
			'transactionTimeout' => self::$rule_out_time,
	    ), true, true);
	    if (empty($info) || $info['code'] != '0'
	    	|| $info['description'] != 'Succeed') {
            $msg = "设定事务规则[{$serviceName}]返回异常:[{$info['description']}]";
	        return Common::dberr($msg);
	    }
	    $this->transactionNum = $info['transactionNum'];

		return ReturnMsg::ret($info['transactionNum']);
	}

    /**
     * 提交事务
     *
     * @return array
     */
	function tCommit() {
		if(!self::checkTranscation() || empty($this->transactionNum)) {
            $msg = '事务未开启,提交失败!';
            return ReturnMsg::err($msg);
        }
		$info = Rule::callUrl(
            RULE_T_COMMIT,
            self::$out_time,
            array(
			    'transactionNum' => $this->transactionNum,
	        ),
            true,
            true
        );
		if($info['code'] != 0 || $info['description'] != 'Succeed') {
            $msg = $this->transactionNum ."事务提交失败.";
			return Common::dbErr($msg);
		}

		return ReturnMsg::ret('事务提交成功!');
	}

    /**
     * 事务回滚
     *
     * @return array
     */
	function tRoolback() {
		if(!self::checkTranscation() || empty($this->transactionNum)) {
            $msg = '事务未开启,回滚失败!';
            return ReturnMsg::err($msg);
        }
		$info = Rule::callUrl(
            RULE_T_ROLLBACK,
            self::$out_time,
            array(
			    'transactionNum' => $this->transactionNum,
	        ),
            true,
            true
        );
		if($info['code'] != 0 || $info['description'] != 'Succeed') {
            $msg = $this->transactionNum . "事务回滚失败.";
			return Common::dbErr($msg);
		}

		return ReturnMsg::ret('事务回滚成功!');
	}

    /**
     * 批量入库
     *
     * @param string $table 表
     * @param array $data data
     *
     * @return array
     */
	function batchInsert($table, $data) 
    {
        if(empty($data) || empty($table)) {
            return 0;
        }
        $ret = array(
			'count'     => 0,
			'insert_id' => array(),
        );
        $batch_data = array_chunk($data, self::$RuleMaxHandle);
        foreach($batch_data as $v) {
            $info = self::query($table, $v, "batch_insert");
            //if($result['error'] != 0) {
	    		//Common::dbErr('批量更新数据更新失败!'); # 只需做个记录,仍需向下处理
	    		//continue;
	    	//}
            foreach($info['msg'] as $v1) {
                $ret['count'] += $v1['updateCount'];
                $ret['insert_id'][] = $v1['generateKey'];
            }
        }

        return $ret;
    }

    /**
     * 批量更新
     *
     * @param string $table 表
     * @param array $data 参数
     * @param array $where 条件
     * @param string $transactionNum 事务号
     *
     * @return array
     */
    function batchUpdate($table, $data, $where, $transactionNum = '')
    {
        if(empty($table) || empty($data) || empty($where)
            || count($data) != count($where)) {
            return ReturnMsg::err('参数信息有误!');
        }
		$up_info = array();
	    foreach ($data as $k => $v) {
	        $param = array(
	            'type'        => 'U',
	            'serviceName' => $table,
	            'set'         => (Object) $v,
	            'where'       => $where[$k] 
	                ? self::whereChangeForRule($where[$k]) 
	                : (Object) $where[$k],
            );
	        $up_info[] = $param;
	    }
	    $transaction = empty($transactionNum) ? 0 : 1;
	    $batch_data = array_chunk($up_info, self::$RuleMaxHandle);
	    foreach ($batch_data as $v) {
	    	$params = array(
				'actions'        => $v,
				'transaction'    => $transaction,
	        );
	        if($params['transaction']) {
	        	$params['transactionNum'] = $transactionNum;
	        }
	    	$result = Rule::callUrl(
                WRITE_URL,
                self::$out_time,
                $params,
                true,
                true
            );
	    	if($result['code'] != 0) {
                $msg = '批量更新数据更新失败!' . $result['description'];
	    		Common::dbErr($msg); # 只需做个记录,仍需返回结果
	    		continue;
	    	}
	    }

        return array(
			'error' => $result['code'] == 0 && $result['description'] == 'Succeed' ? 0 : 1,
			'msg'   => $result['results'],
		);
    }

    /**
     * 批量更新
     *
     * @param string $serviceName 服务名
     * @param array $data 参数
     * @param string $type 类型
     * @param array $where 条件
     *
     * @return array
     */
    function query($serviceName, $data, $type, $where = array()) {
		$type = strtolower($type);
		$type_config_rule = array(
			'insert'       => 'generateKey', 
			'replace'      => 'generateKey', 
			'update'       => 'updateCount', 
			'batch_insert' => '',
			'delete'       => '',
		);
		if(empty($serviceName) 
			|| !array_key_exists($type, $type_config_rule)
			|| (empty($data) && $type != 'delete')) {
			return ReturnMsg::err('参数有误!');
		}
		$type_config = $type_config_rule[$type];

		$ret = self::ruleCore($type, $serviceName, self::whereChangeForRule($where), $data, 1);
		if($ret['code'] != 0) {
			return Common::dbErr($type . '操作失败:' . $ret['description']);
		}

		return empty($type_config) || !isset($ret['results']['0'][$type_config])
			? ReturnMsg::ret($ret['results'])
			: ReturnMsg::ret($ret['results']['0'][$type_config]);
	}

    /**
     * 批量更新
     *
     * @param string $type 操作类型
     * @param array $service 服务
     * @param array $conditions 条件
     * @param array $data 参数
     *
     * @return array
     */
	function ruleCore($type, $service, $conditions = array(), $data = array()){
		$type = strtolower($type);
		$type_config = array(
			'insert'       => 'C',
			'replace'      => 'R',
			'batch_insert' => 'C',
			'update'       => 'U',
			'delete'       => 'D',
		);
		$action_type = $type_config[$type];
		// ERP Table 插入时 自动添加主键
		if ($type == 'insert' || $type == 'batch_insert') {
			$error= '';
			if( ! ErpTableIdHelper::detect_and_handle_ERP_table($service, $data, $type, $error) ){
				return ReturnMsg::err($error);
			}
		}
		if(empty($action_type) || empty($service)) {
			return ReturnMsg::err('数据基本信息有误');
		}
		# 对非批量插入的数据进行处理
	    if ($type != 'batch_insert') { $data = array($data); }
	    # 初始化参数
		$params = array(
			'actions'     => array(),
			'transaction' => '',
		);
		foreach ($data as $fval) {
	        $param = array(
	            'type'        => $action_type,
	            'serviceName' => $service,
	            'set'         => (Object) $fval,
	            'where'       => (Object) $conditions,
            );
	        $params['actions'][] = $param;
	    }
	    # 加入事务控制逻辑
	    if(self::checkTranscation() && !empty($this->transactionNum)) {
			$params['transaction']    = 1;
			$params['transactionNum'] = $this->transactionNum;
	    }

	    return Rule::callUrl(WRITE_URL, self::$out_time, $params, true, true);
	}

    /**
     * 将where条件转换为规则支持的样子
     *
     * @param array $where 条件
     *
     * @return array
     */
	function whereChangeForRule($where) {
		if(empty($where)) { return array(); }
		$rule_data = array();
		foreach($where as $v) {
			$value = array(
				'key'   => $v['key'],
				'op'    => $v['op'],
				'value' => $v['value'],
			);
			if(!empty($v['prepend'])) {
				$rule_data[$v['prepend']]['prepend'] = $v['prepend'];
				$rule_data[$v['prepend']]['data'][]  = $value;
			} else {
				$rule_data['and']['data'][]  = $value;
				$rule_data['and']['prepend'] = 'and';
			}
		}
		$ret  = array();
		$temp = &$ret;
		do{
			$con  = array_shift($rule_data);
			$temp = array(
                'prepend'    => $con['prepend'],
                'conditions' => $con['data'],
                'wheres'     => array(),
			);
			$temp = &$temp['wheres'];
		}while(!empty($rule_data));

		return $ret;
	}

    /**
     * 针对单条数据的更新 针对事务支持请传入$obj
     *
     * @param string $table_name 表名
     * @param array $data 数据
     * @param int $id ID
     * @param string $id_name ID名
     * @param object $obj object
     *
     * @return array
     */
	function updateTableById($table_name = '', $data = array(), $id = 0, $id_name = 'id', $obj = '') {
		if(empty($table_name) || empty($data) || empty($id) || empty($id_name)) {
			return ReturnMsg::err('参数信息有误');
		}
		if(is_object($obj)) {
			$ret = $obj->db->query($table_name, $data, 'UPDATE', array(
				array(
	                'key'   => $id_name,
	                'op'    => '=',
	                'value' => $id,
	            )
			));
		} else {
			$ret = self::query($table_name, $data, 'UPDATE', array(
				array(
	                'key'   => $id_name,
	                'op'    => '=',
	                'value' => $id,
	            )
			));
		}

		return $ret;
	}

    /**
     * 针对多条数据的更新 针对事务支持请传入$obj
     *
     * @param string $table_name 表名
     * @param array $data 数据
     * @param array $id_ar ID数组
     * @param string $id_name ID名
     * @param object $obj object
     *
     * @return array
     */
	function updateTableByIdAr($table_name = '', $data = array(), $id_ar = array(), $id_name = 'id', $obj = '') {
		if(empty($table_name) 
			|| empty($data) 
			|| empty($id_ar) 
			|| !is_array($id_ar) 
			|| empty($id_name)
		) {
			return ReturnMsg::err('参数信息有误');
		}

		$transactionNum = is_object($obj) && !empty($obj->db->transactionNum) 
			? $obj->db->transactionNum 
			: '';

		$batch_data  = array();
		$batch_where = array();
		foreach($id_ar as $id) {
			if(empty($id)) { continue; }

			$batch_data[] = $data;
			$batch_where[] = array(
				array(
	                'key'   => $id_name,
	                'op'    => '=',
	                'value' => $id,
	            )
			);
		}

		return self::batchUpdate($table_name, $batch_data, $batch_where, $transactionNum);
	}
}