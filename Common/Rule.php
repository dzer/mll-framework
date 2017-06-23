<?php

namespace Mll\Common;

use Mll\Mll;
use Mll\Exception;
use Mll\Curl\Curl;

/**
 * 规则调用类
 *
 * @package Mll\Cache
 * @author wang zhou <zhouwang@mll.com>
 * @since 1.0
 */
class Rule
{
    private static $RuleAliasMemkey = 'RuleAliasMemk_';
    private static $ruleMethodSave = '';

    /**
     * 调用规则
     *
     * @param string $alias_name 规则名称
     * @param array $param 请求参数
     * @param bool|int $needAll 是否全部
     * @param int $time 超时时间
     *
     * @return void
     */
    public static function callRule($alias_name, $param = array(), $needAll = 1, $time = 30)
    {
        $serviceName = self::getRuleName($alias_name);
        if (!$serviceName) {
            $msg = '此别名[' . $alias_name . ']并没有找到对应的规则!';
            return ReturnMsg::err($msg);
        }
        $data = array(
            'serviceName' => $serviceName,
            'param' => empty($param) && !is_object($param) ? (object)$param : $param,
            'needAll' => (int)$needAll,
        );
        $result = self::callUrl(RULE_URL, $time, $data, true, true);
        $logRuleArr = explode(',', self::_getSaveLogRuleMethod());
        if (in_array($serviceName, $logRuleArr)) {
            $message = 'CURL规则参数!';
            $logContent = array(
                'method' => $serviceName,
                'param' => addslashes(json_encode($data)),
                'result' => addslashes(json_encode($result)),
            );
            Mll::app()->log->info($message,$logContent,LOG_TYPE_RULE);
        }
        if (empty($result) || (isset($result['code']) && $result['code'] !== '0')) {
            $msg = '规则[' . $serviceName . ']返回异常:[' . $result['description'] . ']';
            return ReturnMsg::err($msg);
        }
        return \Mll\Common\ReturnMsg::ret($result['rows']);
    }
    /**
     * 抛出异常
     *
     * @param string $msg 异常信息
     *
     * @throws \ErrorException
     */
    public static function throwMst($msg){
        throw new \ErrorException($msg);
    }
    /**
     * 查询需要记录日志的规则
     *
     * @return void
     */
    private static function _getSaveLogRuleMethod() {
        if(!empty(self::$ruleMethodSave)) { return self::$ruleMethodSave; }
        $var = 'rule_method_save';
        $info = self::getDBVariableFromRule($var);
        return self::$ruleMethodSave = empty($info) ? 'NO_HAVE_RULE_SAVE_LOG' : $info;
    }
    /**
     * 取系统变量
     *
     * @param array $info 规则名称
     *
     * @return mixed
     */
    static function getDBVariableFromRule($info = array())
    {
        if(empty($info)) {
            return false;
        }
        $data = array(
            'serviceName' => 'Order_BUV1_variabletable',
            'param' => array(
                'var_name_str' => (string) is_array($info) ? "'" . implode("','", $info) . "'" : "'{$info}'",
            ),
            'needAll' => 1,
        );
        $config_info = self::callUrl(
            RULE_URL,
            30,
            $data,
            true,
            true
        );
        if(isset($config_info['code']) && !empty($config_info['code'])) {
            return false;
        }
        if(!is_array($info)) {
            return isset($config_info['rows']['0']['var_value']) ? $config_info['rows']['0']['var_value'] : '';
        }
        $ret = array();
        if(isset($config_info['rows']) && $config_info['rows']){
            foreach($config_info['rows'] as $v) {
                if(!isset($v['var_name'])){
                    continue;
                }
                $ret[$v['var_name']] = isset($v['var_value']) ? $v['var_value'] : '';
            }
        }
        return $ret;
    }
    /**
     * 获取缓存规则名
     *
     * @param string $rule_name 规则名称
     *
     * @return void
     */
    public static function getRuleName($rule_name) {
        if (empty($rule_name)) {
            return false;
        }
        $adminCache = MLL::app()->cache->cut('memcache.admin');
        //别名缓存一小时
        $memkey = self::$RuleAliasMemkey . $rule_name;
        $rule_name_mem = $adminCache->get($memkey);
        if (false === $rule_name_mem) {
            $rule_name_mem = $rule_name;
            $ret = self::RuleConfig($rule_name);
            if (isset($ret['rows']['0']['name'])) {
                $rule_name_mem = $ret['rows']['0']['name'];
            }
            $adminCache->set($memkey, $rule_name_mem, false, 3600);
        }
        return $rule_name_mem;
    }
    /**
     * 获取规则配置
     *
     * @param string $rule_name 规则名称
     *
     * @return mixed
     */
    private static function RuleConfig($rule_name){
        $data = array (
            'serviceName' => 'BUV1_RuleConfig',
            'param' => array (
                'alias_name' => $rule_name
            ),
            'needAll' => 1
        );
        return self::callUrl(
            RULE_ENGINE_URL,
            30,
            $data,
            true,
            true
        );
    }
    /**
     * 获取缓存规则名
     *
     * @param string $url 规则名称
     * @param int $time 超时时间
     * @param array $data 规则参数
     * @param bool $json 是否json格式
     * @param bool $for_result 是否只取curl结果
     *
     * @return void
     */
    public static function callUrl($url, $time = 30, $data = array(), $json = false, $for_result = false)
    {
        $curl = new Curl();
        $headers = array(
            'Connection' => 'Keep-Alive',
            'Keep-Alive' => '60'
        );
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_FOLLOWLOCATION => TRUE,
        );
        if (!empty($data)) {
            if ($json) {
                $headers['Content-Type'] = 'application/json; charset=utf-8';
                $data = json_encode($data);
            } else {
                $headers ['Content-Type'] = 'application/x-www-form-urlencoded';
                $data = http_build_query($data);
            }
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = $data;
        } else {
            $options[CURLOPT_POST] = 0;
        }
        $options[CURLOPT_NOBODY] = FALSE;
        $options[CURLOPT_TIMEOUT] = $time;
        $user_name = isset($_SESSION ['admin_name']) ? $_SESSION ['admin_name'] : '';
        if (empty($user_name)) {
            $user_name = isset($_SESSION ['user_name']) ? $_SESSION ['user_name'] : '';
        }
        $referer = $_SERVER ["HTTP_REFERER"] ? $_SERVER ["HTTP_REFERER"] : $_SERVER ["REQUEST_URI"];
        $headers['ruleReferer'] = "{$referer}&_usernm_={$user_name}";
        $headers['ruleUri'] = "{$_SERVER["REQUEST_URI"]}&_usernm_={$user_name}";
        $options[CURLOPT_HTTPHEADER] = self::buildHeaderArray($headers);
        $curl->setOpts($options);
        list ( $s_usec, $s_sec ) = explode(" ", microtime());
        $result = $curl->exec();
        list ( $e_usec, $e_sec ) = explode(" ", microtime());
        $total_time = ( float ) (($e_sec - $s_sec) + ($e_usec - $s_usec));
        $http_code = $curl->getInfo(CURLINFO_HTTP_CODE);
        if ($for_result) {
            $back_json = json_decode($result, true);
            return $back_json ? $back_json : $result;
        }
        return array (
            'url' => $url,
            'result' => $result,
            'http_code' => $http_code,
            'time_cost' => $total_time
        );
    }
    /**
     * 拼接header
     *
     * @param array $headers header数组
     *
     * @return string
     */
    private static function buildHeaderArray($headers) {
        $output = array ();
        foreach ($headers as $key => $header) {
            $output [] = "{$key}:{$header};";
        }
        return $output;
    }
}
