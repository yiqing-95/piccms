<?php

/**
 * Session Memcache存贮
 * 
 * @author 小鱼哥哥
 *         @time 2011-9-2 15:30
 * @version 1.0
 */
class Session_Memcache extends Session {

    /**
     *
     * @var string 键前缀
     */
    const CACHE_KEY = 'SESSION_';

    /**
     *
     * @var object 数据库对象
     */
    private $_cache = null;

    /**
     * 开始Session
     *
     * @return void
     */
    public function __construct($cache){
        $this->_cache = $cache;
        $this->_lifetime = ini_get('session.gc_maxlifetime');
        session_set_save_handler(array(
                $this,
                '_open'
        ),array(
                $this,
                '_close'
        ),array(
                $this,
                '_read'
        ),array(
                $this,
                '_write'
        ),array(
                $this,
                '_destroy'
        ),array(
                $this,
                '_gc'
        ));
    }
    
    // _open
    public function _open($savePath, $name){
        return true;
    }
    
    // _close
    public function _close(){
        $this->_gc($this->_lifetime);
        return true;
    }
    
    // _read
    public function _read($id){
        $res = $this->_cache->get(self::CACHE_KEY . $id);
        return $res ? $res:'';
    }
    
    // _write
    public function _write($id, $value){
        $flag = $this->_cache->set(self::CACHE_KEY . $id,$value,
                $this->_lifetime);
        return $flag;
    }
    
    // _destroy
    public function _destroy($id){
        $flag = $this->_cache->delete(self::CACHE_KEY . $id);
        return $flag;
    }
    
    // _gc
    public function _gc($lifetime){}
}
