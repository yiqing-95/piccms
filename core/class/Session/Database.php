<?php

/**
 * Session Database存贮
 * 
 * @author 小鱼哥哥
 *         @time 2011-9-2 15:30
 * @version 1.0
 */
class Session_Database extends Session {

    /**
     *
     * @var string Session表名
     */
    private $_table = '#@_session';

    /**
     *
     * @var object 数据库对象
     */
    private $_db = null;

    public function __construct($db, $table){
        $this->_db = $db;
        $this->_table = $table;
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
        $res = $this->_db->table($this->_table)
            ->where(array(
                'id'=>$id
        ))
            ->getOne();
        return $res ? $res['value']:'';
    }
    
    // _write
    public function _write($id, $value){
        $data = array(
                'id'=>$id,
                'value'=>$value,
                'exp'=>time() + $this->_lifetime
        );
        $flag = $this->_db->table($this->_table)->replace($data);
        return $flag;
    }
    
    // _destroy
    public function _destroy($id){
        $flag = $this->_db->table($this->_table)
            ->where("id = '$id'")
            ->delete();
        return $flag;
    }
    
    // _gc
    public function _gc($lifetime){
        $flag = $this->_db->table($this->_table)
            ->where("exp < " . time())
            ->delete();
        return $flag;
    }
}
