<?php

/**
 * 数据库扩展功能
 * 
 * @author 小鱼哥哥
 *         @time 2011-9-2 15:27
 * @version 1.0
 */
class Db_Plus {

    public $db = null;

    public function __construct(& $db){
        $this->db = $db;
    }

    /**
     * 列出所有表名
     * 
     * @param
     *            mixed
     * @return void
     */
    public function showTables($dbname = null){
        if ($dbname){
            $sql = "SHOW TABLES FROM `$dbname`";
        }else{
            $sql = "SHOW TABLES";
        }
        $res = $this->db->getRows($sql);
        $arr = array();
        foreach($res as $value){
            $arr[] = reset($value);
        }
        return $arr;
    }

    /**
     * 备分数据
     * 
     * @param
     *            mixed
     * @return void
     */
    public function dumpSql($tables, $path, $size = 2048){
        $sql = '';
        $p = 1;
        $random = mt_rand(1000,9999);
        foreach($tables as $table){
            $query = $this->db->query("SELECT * FROM `$table`");
            $sql .= "TRUNCATE TABLE `$table`;\n";
            while($row = $this->db->fetch($query)){
                $sql .= $this->getInsertSql($table,$row);
                if (strlen($sql) >= $size * 1000){
                    $filename = $path . date('Ymd') . "_{$random}_{$p}.sql";
                    Ext_File::write($filename,$sql);
                    $p++;
                    $sql = '';
                }
            }
        }
        if ($sql){
            $filename = $path . date('Ymd') . "_{$random}_{$p}.sql";
            Ext_File::write($filename,$sql);
        }
        return $p;
    }

    /**
     * 获取InsertSql
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getInsertSql($table, $row){
        $sql = "INSERT INTO `{$table}` VALUES (";
        $values = array();
        foreach($row as $value){
            $values[] = "'" . mysql_real_escape_string($value) . "'";
        }
        $sql .= implode(', ',$values) . ");\n";
        return $sql;
    }

    /**
     * 批量运行SQL
     * 
     * @param
     *            mixed
     * @return void
     */
    public function batQuery($sqlStr){
        $sqlStr = str_replace("\r\n","\n",$sqlStr);
        $num = 0;
        foreach(explode(";\n",trim($sqlStr)) as $sql){
            if ('#' != $sql{0}){
                $this->db->query($sql);
                $num++;
            }
        }
        return $num;
    }

    /**
     * 获取SQL备分文件列表
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getSqlFile($path){
        // $arr = glob(Wee::$config['data_path'] . 'db_backup/*.sql');
        $arr = Ext_Dir::getDirList(Wee::$config['data_path'] . 'db_backup',
                Ext_Dir::TYPE_FILE,array(),array(
                        'sql'
                ));
        $sqlFile = array();
        foreach($arr as $value){
            $value = Wee::$config['data_path'] . 'db_backup/' . $value;
            $data = array();
            if (preg_match("/(\d+_\d+)_(\d+)\.sql/i",basename($value),$tmp)){
                if (1 == $tmp[2]){
                    $data['filename'] = $tmp[0];
                    $data['pre'] = $tmp[1];
                    $data['page'] = $tmp[2];
                    $data['maketime'] = date('Y-m-d H:i:s',filemtime($value));
                    $data['filesize'] = Ext_Math::lifeByte(filesize($value));
                    $sqlFile[] = $data;
                }
            }
        }
        return $sqlFile;
    }

    /**
     * 获取某个卷号所有的文件
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getSamePreFile($path, $filename){
        $arr = array();
        if (preg_match("/^(\d+_\d+)_(\d+)\.sql$/i",basename($filename),$tmp)){
            $allFiles = Ext_Dir::getDirList(
                    Wee::$config['data_path'] . 'db_backup',Ext_Dir::TYPE_FILE,
                    array(),array(
                            'sql'
                    ));
            if ($allFiles){
                foreach($allFiles as $value){
                    if (preg_match("/^{$tmp[1]}_(\d+)\.sql$/",$value,$tmpb)){
                        $arr[] = $path . $value;
                    }
                }
            }
            /*
             * // 尼玛 有的服务器不支持 glob $files = glob($path . $tmp[1] . '_*.sql'); if
             * (!empty($files)) { $arr = $files; }
             */
        }
        return $arr;
    }
}