<?php

/**
 * MYSQLI 数据库驱动
 * 
 * @author 小鱼哥哥
 *         @time 2011-9-2 15:27
 * @version 1.0
 */
class Db_Mysqli extends Db {

    /**
     *
     * @var string 当前使用的库
     */
    private $_currentDb;

    /**
     *
     * @var resource 当前数据库连接
     */
    private $_link;

    /**
     *
     * @var string 当前执行的SQL
     */
    private $_sql;

    /**
     *
     * @var integer 影响条数
     */
    private $_affectRows = 0;

    /**
     *
     * @var integer INSERT操作产生的ID
     */
    private $_insertId = 0;

    /**
     * 初使化连接
     *
     * @param string $host
     *            服务器
     * @param string $port
     *            端口
     * @param string $user
     *            用户名
     * @param string $pass
     *            密码
     * @param string $dbname
     *            数据库名
     * @param Boolean $newLink
     *            是否启用新连接
     * @return void
     */
    public function __construct($host, $port, $user, $pass, $dbname = null){
        if (!function_exists('mysqli_connect')){
            show_error('Mysqli support is disabled');
        }
        $this->_host = $host;
        $this->_port = $port;
        $this->_user = $user;
        $this->_pass = $pass;
        $this->_dbname = $dbname;
        $this->_newLink = Wee::$config['db_new_link'];
        $this->_charset = Wee::$config['db_charset'];
    }

    /**
     * 连接数据库
     *
     * @return void
     */
    public function connect(){
        if (!$this->_link){
            $mysqli = @mysqli_connect($this->_host,$this->_user,$this->_pass,'',
                    $this->_port);
            if ($mysqli->connect_error){
                $this->_showError("$this->_host: 数据库服务器连接失败");
            }
            $this->_link = $mysqli;
            mysqli_autocommit($this->_link,true);
            if ($this->_dbname){
                $this->useDb($this->_dbname);
            }
        }
    }

    /**
     * 选择数据库
     *
     * @param string $dbname
     *            数据库名
     * @return object 当前数据库对象
     */
    public function usedb($dbname){
        $dbname = $this->_dealDbName($dbname);
        if ($dbname != $this->_currentDb){
            if (!$this->_link){
                $this->connect();
            }
            $flag = mysqli_select_db($this->_link,$dbname);
            if (!$flag){
                $this->_showError($dbname . ': 数据库不存在或者无法使用');
            }
            $this->_currentDb = $dbname;
            mysqli_query($this->_link,"SET NAMES '{$this->_charset}'");
        }
        return $this;
    }

    /**
     * 获取当前正在使用的库
     *
     * @return mixed
     */
    public function getCurrentDb(){
        return $this->_currentDb;
    }

    /**
     * 查询所有数据
     *
     * @param string $sql
     *            SQL语句
     * @param string $asKey
     *            用做键值的字段名
     * @return array 查询结果
     */
    public function getRows($sql, $asKey = null){
        $query = $this->query($sql);
        $res = array();
        while($value = mysqli_fetch_assoc($query)){
            if ($asKey){
                $res[$value[$asKey]] = $value;
            }else{
                $res[] = $value;
            }
        }
        return $res;
    }

    /**
     * 获得查询结果
     * 
     * @param resource $query
     *            查询结果
     * @return void
     */
    public function fetch($query){
        return mysqli_fetch_assoc($query);
    }

    /**
     * 执行SQL
     *
     * @param string $sql
     *            SQL语句
     * @return resource
     */
    public function query($sql){
        if (!$this->_link){
            $this->connect();
        }
        $sql = trim($sql);
        $this->_sql = $sql;
        $_runStartTime = microtime(true);
        $isInsert = (0 === stripos($sql,'INSERT INTO'));
        $rs = mysqli_query($this->_link,$sql);
        $_runEndTime = microtime(true);
        
        // 纪录查询信息
        $this->_recordSqlQuery($sql,$_runStartTime,$_runEndTime);
        
        // 返回查询结果
        if ($rs){
            $this->_affectRows = mysqli_affected_rows($this->_link);
            if ($isInsert){
                $this->_insertId = mysqli_insert_id($this->_link);
            }
            return $rs;
        }else{
            $errno = mysqli_errno($this->_link);
            if ($this->_queryError){
                $this->_showError(
                        "SQL Query Error(" . mysqli_error($this->_link) .
                                 " [$errno]): $sql");
            }
            return false;
        }
    }

    /**
     * 获取最后插入数据ID
     *
     * @return integer 最后插入数据ID
     */
    public function insertId(){
        return $this->_insertId;
    }

    /**
     * 获取影响行数
     *
     * @return integer 影响行数
     */
    public function affectRows(){
        return $this->_affectRows;
    }

    /**
     * 关闭数据库连接
     *
     * @return Boolean
     */
    public function close(){
        if ($this->_link){
            $rs = mysqli_close($this->_link);
            if ($rs){
                $this->_link = null;
            }
        }
    }

    /**
     * 获取最后执行的SQL
     *
     * @param string $args            
     * @return string 最后执行的SQL
     */
    public function lastSql(){
        return $this->_sql;
    }
}