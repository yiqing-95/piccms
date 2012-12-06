<?php

/**
 * 数据库抽象层
 * 
 * @author 小鱼哥哥
 *         @time 2011-9-2 15:25
 * @version 1.0
 */
abstract class Db {

    /**
     *
     * @var array 链式条件
     */
    private $_cond = array();

    /**
     *
     * @var string 服务器
     */
    protected $_host;

    /**
     *
     * @var string 端口
     */
    protected $_port;

    /**
     *
     * @var string 用户名
     */
    protected $_user;

    /**
     *
     * @var string 密码
     */
    protected $_pass;

    /**
     *
     * @var string 默认使用的库
     */
    protected $_dbname;

    /**
     *
     * @var boolean 打开新的连接
     */
    protected $_newLink = false;

    /**
     *
     * @var string 数据编码
     */
    protected $_charset = 'utf8';

    /**
     *
     * @var bool 是否显示查询错误, 默认显示
     */
    protected $_queryError = true;

    /**
     *
     * @var Boolean 事务中
     */
    private $_inTransaction = false;

    /**
     *
     * @var integer 事务开启数
     */
    private $_transactionNum = 0;

    /**
     * 设置错误状态
     * 
     * @param
     *            mixed
     * @return void
     */
    public function queryError($queryError = true){
        $this->_queryError = $queryError;
        return $this;
    }

    /**
     * 连接数据库
     */
    abstract public function connect();

    /**
     * 选择数据库
     */
    abstract public function useDb($db_name);

    /**
     * 获取当前正在使用的库
     */
    abstract public function getCurrentDb();

    /**
     * 根据SQL获取查询结果
     */
    abstract public function getRows($sql, $asKey = null);

    /**
     * 执行SQL
     */
    abstract public function query($sql);

    /**
     * 获取查询结果
     */
    abstract public function fetch($query);

    /**
     * 最后插入数据ID
     */
    abstract public function insertId();

    /**
     * 影响行数
     */
    abstract public function affectRows();

    /**
     * 最后执行的SQL
     */
    abstract public function lastSql();

    /**
     * 关闭数据库连接
     */
    abstract public function close();

    /**
     * 根据SQL获取查询结果
     *
     * @param string $asKey
     *            用做键值的字段名
     * @return array 查询结果
     */
    public function getAll($asKey = null){
        $res = $this->getRows($this->_dealSelectSql(),$asKey);
        return $res;
    }

    /**
     * 查询一条数据
     *
     * @return array 查询结果
     */
    public function getOne(){
        $res = $this->getRows($this->_dealSelectSql());
        return isset($res[0]) ? $res[0]:false;
    }

    /**
     * 更新数据
     *
     * @param array $data
     *            待更新的数据
     * @return Boolean
     */
    public function update($data){
        if (is_array($data)){
            $set = array();
            foreach($data as $key=>$value){
                $set[] = "`$key` = '$value'";
            }
            $set = implode(", ",$set);
        }else{
            $set = $data;
        }
        $where = $this->_dealWhere();
        $table = $this->_dealTable();
        if ('' == $where){
            $this->_showError(
                    __METHOD__ . ' expects at least [where] parameters');
            return false;
        }
        $sql = "UPDATE $table SET $set $where";
        return $this->query($sql);
    }

    /**
     * 删除数据
     *
     * @return Boolean
     */
    public function delete(){
        $where = $this->_dealWhere();
        $table = $this->_dealTable();
        if ('' == $where){
            $this->_showError(
                    __METHOD__ . ' expects at least [where] parameters');
            return false;
        }
        $sql = "DELETE FROM $table $where";
        return $this->query($sql);
    }

    /**
     * 插入数据
     *
     * @param array $data
     *            待插入的数据
     * @return Boolean
     */
    public function insert($data){
        return $this->_insertFun($data,'INSERT');
    }

    /**
     * 替换数据
     *
     * @param array $data
     *            待替换的数据
     * @return Boolean
     */
    public function replace($data){
        return $this->_insertFun($data,'REPLACE');
    }

    /**
     * 插入或者替换数据
     *
     * @param string $data
     *            待插入或者替换的数据
     * @param string $fun
     *            操作类型
     * @return Boolean
     */
    private function _insertFun($data, $fun = 'INSERT'){
        $first = reset($data);
        $cols = array();
        if (is_array($first)){
            $cols = array_keys($first);
            $vals = array();
            foreach($data as $value){
                $vals[] = "('" . implode("', '",$value) . "')";
            }
            $vals = implode(", ",$vals);
        }else{
            $cols = array_keys($data);
            $vals = "('" . implode("', '",$data) . "')";
        }
        $cols = "(`" . implode("`, `",$cols) . "`)";
        $table = $this->_dealTable();
        $sql = "$fun INTO $table $cols VALUES $vals";
        return $this->query($sql);
    }

    /**
     * 事务开始
     *
     * @return void
     */
    public function begin(){
        if (!$this->_inTransaction){
            $this->query('BEGIN');
            $this->_inTransaction = true;
            $this->_transactionNum = 1;
        }else{
            $this->_transactionNum++;
        }
    }

    /**
     * 事务回滚
     *
     * @return void
     */
    public function rollBack(){
        if ($this->_inTransaction){
            $this->query('ROLLBACK');
            $this->_inTransaction = false;
            $this->_transactionNum = 0;
        }
    }

    /**
     * 事务提交
     *
     * @return void
     */
    public function commit(){
        if (1 == $this->_transactionNum && $this->_inTransaction){
            $this->query('COMMIT');
            $this->_inTransaction = false;
            $this->_transactionNum = 0;
        }else{
            $this->_transactionNum--;
        }
    }

    /**
     * 获取事务匹配数
     *
     * @return integer 事务匹配数
     */
    public function getTransactionNum(){
        return $this->_transactionNum;
    }

    /**
     * 设置where参数
     *
     * @param string/array $where
     *            where参数
     * @return object 数据库对象
     */
    public function where($where){
        $this->_cond['where'] = $where;
        return $this;
    }

    /**
     * 设置table参数
     *
     * @param string $table
     *            表名
     * @return object 数据库对象
     */
    public function table($table){
        if (Wee::$config['db_table_prefix'] && false !== strpos($table,'#@_')){
            $table = str_replace('#@_',Wee::$config['db_table_prefix'],$table);
        }
        $this->_cond['table'] = $table;
        return $this;
    }

    /**
     * 设置order参数
     *
     * @param string/array $order
     *            order参数
     * @return object 数据库对象
     */
    public function order($order){
        $this->_cond['order'] = $order;
        return $this;
    }

    /**
     * 设置limit参数
     *
     * @param string $limit
     *            limit参数
     * @return object 数据库对象
     */
    public function limit($limit){
        $this->_cond['limit'] = $limit;
        return $this;
    }

    /**
     * 设置offset参数
     *
     * @param string $offset
     *            offset参数
     * @return object 数据库对象
     */
    public function offset($offset){
        $this->_cond['offset'] = $offset;
        return $this;
    }

    /**
     * 设置field参数
     *
     * @param string/array $field
     *            field参数
     * @return object 数据库对象
     */
    public function field($field){
        $this->_cond['field'] = $field;
        return $this;
    }

    /**
     * 设置group参数
     *
     * @param string $group
     *            group参数
     * @return object 数据库对象
     */
    public function group($group){
        $this->_cond['group'] = $group;
        return $this;
    }

    /**
     * 处理真实的库名
     * 
     * @param
     *            string 库别名
     * @return string 真实的库名
     */
    protected function _dealDbName($dbName){
        if (isset(Wee::$config['db_name_alias'][$dbName])){
            $dbName = Wee::$config['db_name_alias'][$dbName];
        }
        return $dbName;
    }

    /**
     * 处理链式操作生成的SQL查询
     *
     * @return string SQL语句
     */
    protected function _dealSelectSql(){
        $table = $this->_dealTable();
        $field = $this->_dealField();
        $where = $this->_dealWhere();
        $order = $this->_dealOrder();
        $group = $this->_dealGroup();
        $limit = $this->_dealLimit();
        $sql = "SELECT $field FROM $table $where $group $order $limit";
        return $sql;
    }

    /**
     * 清除连贯条件
     *
     * @return void
     */
    protected function _clearCond($key){
        $this->_cond[$key] = null;
    }

    /**
     * 处理表名
     *
     * @return string 表名
     */
    protected function _dealTable(){
        return $this->_cond['table'];
    }

    /**
     * 处理字段名
     *
     * @return string 字段名
     */
    protected function _dealField(){
        $fieldList = '*';
        if (!empty($this->_cond['field'])){
            $fieldList = is_array($this->_cond['field']) ? implode(', ',
                    $this->_cond['field']):$this->_cond['field'];
        }
        $this->_clearCond('field');
        return $fieldList;
    }

    /**
     * 处理WHERE条件
     *
     * @return string where条件
     */
    protected function _dealWhere(){
        $where = '';
        if (!empty($this->_cond['where'])){
            if (is_array($this->_cond['where'])){
                $tmpArr = array();
                foreach($this->_cond['where'] as $key=>$value){
                    if (is_numeric($key)){
                        $tmpArr[] = $value;
                    }else{
                        if (is_array($value)){
                            $tmpArr[] = "$key IN ('" . implode("', '",$value) .
                                     "')";
                        }else{
                            $tmpArr[] = "$key = '$value'";
                        }
                    }
                }
                $where = "WHERE " . implode(' AND ',$tmpArr);
            }else{
                $where = "WHERE {$this->_cond['where']}";
            }
        }
        $this->_clearCond('where');
        return $where;
    }

    /**
     * 处理ORDER条件
     *
     * @return string order 条件
     */
    protected function _dealOrder(){
        $order = '';
        if (!empty($this->_cond['order'])){
            $order = "ORDER BY {$this->_cond['order']}";
        }
        $this->_clearCond('order');
        return $order;
    }

    /**
     * 处理GROUP条件
     *
     * @return string group条件
     */
    protected function _dealGroup(){
        $group = '';
        if (!empty($this->_cond['group'])){
            $group = "GROUP BY {$this->_cond['group']}";
        }
        $this->_clearCond('group');
        return $group;
    }

    /**
     * 处理LIMIT条件
     *
     * @return string limit条件
     */
    protected function _dealLimit(){
        $limit = '';
        if (!empty($this->_cond['limit'])){
            $limit = " LIMIT {$this->_cond['limit']}";
        }
        $this->_clearCond('limit');
        return $limit;
    }

    /**
     * 处理ID数组
     *
     * @param string/array $id
     *            ID数据
     * @param boolean $is_str
     *            是否为字符串
     * @return string 处理后的ID数据字符串
     */
    protected function _dealId($id, $is_str = false){
        $str = '';
        if (is_array($id)){
            if ($is_str){
                $str = "IN ('" . implode("', '",$id) . "')";
            }else{
                $str = "IN (" . implode(", ",$id) . ")";
            }
        }else{
            $str = "= $id";
        }
        return $str;
    }

    /**
     * 抛出错误信息
     *
     * @param string $errmsg
     *            错误消息
     * @return void
     */
    protected function _showError($errmsg = 'error'){
        throw new Error($errmsg,Error::DB_ERROR);
    }

    /**
     * 纪录SqlQuery信息
     * 
     * @param
     *            mixed
     * @return void
     */
    protected function _recordSqlQuery($sql, $startTime, $endTime){
        $runTime = round($endTime - $startTime,5);
        // 记录查询信息
        if (Wee::$config['debug_mode']){
            Wee::$box['sqlQuery'][] = array(
                    'sql'=>$sql,
                    'time'=>date('Y-m-d H:i:s'),
                    'runTime'=>$runTime
            );
        }
        // 纪录慢查询日志
        if (true == Wee::$config['db_sql_log']){
            if ($runTime > Wee::$config['db_slow_sql_time']){
                $sqlInfo = array(
                        'host'=>$this->_host,
                        'db'=>$this->_dbname,
                        'sql'=>$sql,
                        'runTime'=>$runTime,
                        'time'=>date('Y-m-d H:i:s')
                );
                Logs::sqlQuery($sqlInfo);
            }
        }
    }
}