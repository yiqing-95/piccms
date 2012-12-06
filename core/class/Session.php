<?php

/**
 * Session会话管理
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-27 17:16
 * @version 1.0
 */
abstract class Session {

    /**
     *
     * @var integer 数据过期时间
     */
    protected $_lifetime = 1440;

    /**
     * 开始Session
     *
     * @return void
     */
    public static function start(){
        $handleClass = Wee::$config['session_handle_class'];
        if ('Session_Database' == $handleClass){
            $db = load_db(Wee::$config['session_db_tag']);
            $handle = new Session_Database($db,Wee::$config['session_table']);
        }elseif ('Session_Memcache' == $handleClass){
            $cache = load_cache();
            $handle = new Session_Memcache($cache);
        }
        session_start();
    }
    
    // _open
    abstract public function _open($save_path, $name);
    
    // _close
    abstract public function _close();
    
    // _read
    abstract public function _read($id);
    
    // _write
    abstract public function _write($id, $value);
    
    // _destroy
    abstract public function _destroy($id);
    
    // _gc
    abstract public function _gc($lifetime);

    /**
     * 暂停Session
     *
     * @return void
     */
    public static function pause(){
        session_write_close();
    }

    /**
     * 清空Session
     *
     * @return void
     */
    public static function clear(){
        $_SESSION = array();
        session_destroy();
    }

    /**
     * 设置或者获取当前Session name
     *
     * @param string $name
     *            Session name
     * @return string Session name
     */
    public static function name($name = null){
        return isset($name) ? session_name($name):session_name();
    }

    /**
     * 设置或者获取当前Session ID
     *
     * @param integer $id
     *            Session ID
     * @return integer Session ID
     */
    public static function id($id = null){
        return isset($id) ? session_id($id):session_id();
    }

    /**
     * 设置或者获取当前Session保存路径
     *
     * @param string $path
     *            Session保存路径
     * @return string Session保存路径
     */
    public static function path($path = null){
        return !empty($path) ? session_save_path($path):session_save_path();
    }

    /**
     * 取得的Session值
     *
     * @param string $name
     *            Session键名
     * @return mixed Session值
     */
    public static function get($name){
        return isset($_SESSION[$name]) ? $_SESSION[$name]:null;
    }

    /**
     * 设置Session值
     *
     * @param string $name
     *            Session键名
     * @param string $value
     *            Session值
     * @return true
     */
    public static function set($name, $value){
        if (is_null($value)){
            unset($_SESSION[$name]);
        }else{
            $_SESSION[$name] = $value;
        }
        return true;
    }

    /**
     * 检查Session值是否存在
     *
     * @param string $name
     *            Session键名
     * @return true/false
     */
    public static function isExist($name){
        return isset($_SESSION[$name]);
    }

    /**
     * 删除Session值
     *
     * @param string $name
     *            Session键名
     * @return void
     */
    public static function delete($name){
        unset($_SESSION[$name]);
    }

    /**
     * 设置Session生存周期
     *
     * @param integer $time
     *            秒
     * @return void
     */
    public static function setLifeTime($time = 3600){
        $now = time();
        if ($time > 0)
            $time = $now + $time;
        elseif ($time < 0)
            $time = $now - $time;
        setcookie(self::name(),self::id(),$time,"/");
    }
}
