<?php

/**
 * 控制器基类
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-27 17:15
 * @version 1.0
 */
class Controller {

    /**
     *
     * @var Db 数据库对象
     */
    protected $db;

    /**
     *
     * @var object 缓存对象
     */
    protected $cache;

    /**
     *
     * @var Request_Input 输入数据对象
     */
    protected $input;

    /**
     *
     * @var Request_Output 传出数据对象
     */
    protected $output;

    /**
     * 初始化控制器
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
     * 请求的方法不存在
     * 
     * @param
     *            mixed
     * @return void
     */
    public function __call($action, $args){
        show_msg("$action: The action does not exist");
    }
}