<?php

/**
 * Created by PhpStorm.
 * User: MacBook
 * Date: 16/9/13
 * Time: 13:17
 */
class Session_Redis implements SessionHandlerInterface {

    private $_client; //redis的客户端链接
    private $_ttl;
    private $_prefix;

    private $_sessionData; //用于记录初始化时session的值，如果发生变化，则会执行写入操作，否则不执行写入操作
    /**
     * Session_Redis constructor.
     * @param Redis $client Redis的客户端
     * @param int $ttl session默认的过期时间,单位为秒(s)
     * @param string $prefix SESSION默认的前缀
     */
    public function __construct(\Redis $client, $ttl = 1440, $prefix = 'SESS:') {
        $this->_client  = $client;
        $this->_ttl     = $ttl;
        $this->_prefix  = $prefix;
    }

    /**
     * @param $session_id
     * @return string
     */
    private function _sessionKey($session_id) {
        $session_key    = $this->_prefix.$session_id;
        return $session_key;
    }

    public function close() {
        // TODO: Implement close() method.
        /*$this->_client->close();
        $this->_client = null;*/
        return TRUE;
    }

    /**
     * 删除当前的SESSION
     * @param string $session_id
     * @return bool
     */
    public function destroy($session_id) {
        // TODO: Implement destroy() method.
        $session_key = $this->_sessionKey($session_id);
        return is_int($this->_client->del($session_key)); //如果返回的是整数说明redis发生了错误
    }

    public function gc($maxlifetime) {
        // TODO: Implement gc() method.
        // no action necessary because using EXPIRE
        return TRUE;
    }

    public function open($save_path, $session_id) {
        // TODO: Implement open() method.
        // No action necessary because connection is injected
        // in constructor and arguments are not applicable.
        return TRUE;
    }

    /**
     * 自动生成的session_id
     * @param string $session_id
     * @return string
     */
    public function read($session_id) {
        // TODO: Implement read() method.
        $session_key    = $this->_sessionKey($session_id);
        $data           = $this->_client->get($session_key);
        $this->_client->expire($session_key, $this->_ttl); //更新当前session的到期时间,如果用户一直在访问网站,那么session是不能过期的
        if (is_null($this->_sessionData)) { //如果未初始化过session
            $this->_sessionData = $data; //保存初始化时的值
        }
        return $data === FALSE ? '' : $data;
    }

    /**
     * 写入session数据
     * @param string $session_id
     * @param string $session_data
     * @return void
     */
    public function write($session_id, $session_data) {
        // TODO: Implement write() method.
        $session_key = $this->_sessionKey($session_id);

        if ($session_data === '') {
            return $this->destroy($session_id); //如果session的值被清空了,则删除这个session在redis中的值
        }

        if ($session_data !== $this->_sessionData) { //如果SESSION的值发生了改变,则重写session的值
            $this->_sessionData = $session_data; //同时更新sessionData中初始化的值
            return $this->_client->setex($session_key, $this->_ttl, $session_data); //写入SESSION
        } else {
            return TRUE;
        }
    }
}
