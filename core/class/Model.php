<?php

/**
 * 模型基类
 * 
 * @author 小鱼哥哥
 *         @time 2011-9-2 15:18
 * @version 1.0
 */
class Model {

    /**
     *
     * @var object 数据库对象
     */
    protected $db;

    /**
     *
     * @var object 缓存对象
     */
    protected $cache;

    /**
     *
     * @var object 输入数据对象
     */
    protected $input;

    /**
     *
     * @var object 传出数据对象
     */
    protected $output;

    /**
     *
     * @var mixed 绑定的表
     */
    protected $table = null;

    /**
     *
     * @var mixed 主键名
     */
    protected $pk = null;

    /**
     * 初始化模型
     * 
     * @return void
     */
    public function __construct(){
        $this->input = &Wee::$input;
        $this->output = &Wee::$output;
        if (Wee::$config['cache_auto_start']){
            $this->cache = load_cache();
        }
        if (Wee::$config['db_auto_start']){
            $this->db = load_db();
        }
    }

    /**
     * 插入数据
     * 
     * @param
     *            mixed
     * @return void
     */
    public function add($data){
        $this->_checkTable();
        $rs = $this->db->table($this->table)->insert($data);
        return $rs;
    }

    /**
     * 获取数据
     * 
     * @param
     *            mixed
     * @return void
     */
    public function get($id){
        $this->_checkTable();
        $rs = $this->db->table($this->table)
            ->where(array(
                $this->pkId=>$id
        ))
            ->getOne();
        if (method_exists($this,'getVo')){
            $rs = $this->getVo($rs);
        }
        return $rs;
    }

    /**
     * 获取列表数据
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getList($where = array(), $limit = '', $order = ''){
        $rs = $this->db->table($this->table)
            ->where($where)
            ->limit($limit)
            ->order($order)
            ->getAll();
        if (method_exists($this,'getVo')){
            foreach($rs as & $value){
                $value = $this->getVo($value);
            }
            unset($value);
        }
        return $rs;
    }

    /**
     * 更新数据
     * 
     * @param
     *            mixed
     * @return void
     */
    public function set($id, $data){
        $this->_checkTable();
        $rs = $this->db->table($this->table)
            ->where(array(
                $this->pkId=>$id
        ))
            ->update($data);
        return $rs;
    }

    /**
     * 删除数据
     * 
     * @param
     *            mixed
     * @return void
     */
    public function del($id){
        $this->_checkTable();
        $rs = $this->db->table($this->table)
            ->where(array(
                $this->pkId=>$id
        ))
            ->delete();
        return $rs;
    }

    /**
     * 设置默认表和主键名
     * 
     * @param
     *            mixed
     * @return void
     */
    protected function setTable($table, $pkId){
        $this->table = $table;
        $this->pkId = $pkId;
    }

    /**
     * 检查表和主键名是否设定
     * 
     * @param
     *            mixed
     * @return void
     */
    private function _checkTable(){
        if (!$this->table || !$this->pkId){
            show_error('$table or $pkId values are not set');
        }
    }
}