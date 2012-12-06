<?php

/**
 * 日志管理类
 * 
 * @author 小鱼哥哥
 *         @time 2011-9-2 15:26
 * @version 1.0
 */
class Logs {

    /**
     *
     * @var integer 日志文件最大容量
     */
    public static $maxFileSize = 10000000;

    /**
     * 写入日志到文件
     *
     * @param string $logContent
     *            日志内容
     * @param string $logFile
     *            日志文件路径
     * @return void
     */
    public static function write($logContent, $logFile){
        if (is_file($logFile) && filesize($logFile) > self::$maxFileSize){
            Ext_File::write($logFile,$logContent);
        }else{
            Ext_File::write($logFile,$logContent,FILE_APPEND);
        }
    }

    /**
     * 纪录数据库错误日志
     *
     * @param mixed $err
     *            错误信息
     * @return void
     */
    public static function errorDbLog($err){
        if (is_array($err)) $err = implode("\n",$err);
        $logFile = Logs::getLogFile('error_db');
        $logContent = "\n[ " . date('Y-m-d H:i:s') . " ]\n" . $err . "\n";
        self::write($logContent,$logFile);
    }

    /**
     * 纪录错误日志
     *
     * @param mixed $err
     *            错误信息
     * @return void
     */
    public static function errorCodeLog($err){
        if (is_array($err)) $err = implode("\n",$err);
        $logFile = Logs::getLogFile('error_code');
        $logContent = "\n[ " . date('Y-m-d H:i:s') . " ]\n" . $err . "\n";
        self::write($logContent,$logFile);
    }

    /**
     * 纪录SQL查询日志
     *
     * @param mixed $info
     *            SQL信息
     * @return void
     */
    public static function sqlQuery($info){
        $logContent = "HOST: {$info['host']} | DB: {$info['db']} | Run time: {$info['runTime']} | SQL: {$info['sql']}\n";
        $logFile = Logs::getLogFile('sql_query');
        $logContent = "\n[ " . $info['time'] . " ]\n" . $logContent;
        self::write($logContent,$logFile);
    }

    /**
     * 获取日志文件路径
     *
     * @param string $logId
     *            日志类型
     * @param string $time
     *            时间
     * @return string 日志文件路径
     */
    public static function getLogFile($logId, $time = null){
        if (!$time) $time = Ext_Date::now();
        if (Wee::REQUEST_SERVER == Wee::$requestType){
            $logPath = Wee::$config['data_path'] . 'server/' .
                     Ext_Date::format($time,'Ymd');
        }else{
            $logPath = Wee::$config['data_path'] . 'client/' .
                     Ext_Date::format($time,'Ymd');
        }
        if (!is_dir($logPath)){
            Ext_Dir::mkdirs($logPath);
        }
        $logFile = $logPath . '/' . $logId . '.log';
        return $logFile;
    }
}