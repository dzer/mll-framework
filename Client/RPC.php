<?php


class RPC
{
    private $_rpc;

    public function run($data, $fd = null)
    {
        if ($this->_rpc === null) {
            $this->_rpc = new \Yar_Client(ZConfig::getField('socket', 'rpc_host'));
        }
        return $this->_rpc->api($data);

    }

}
