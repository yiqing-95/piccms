<?php

/**
 * 进程任务管理器
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-27 17:19
 * @version 1.0
 */
class Ext_Task {

    /**
     * UNIX/LINUX 下获取正在运行的进程 ID 数组
     *
     * @param string $script
     *            执行的脚本名
     * @param string $bin
     *            命令中的解析器路径, 如 ./php python
     * @return array
     */
    public static function getTaskId($script, $bin){
        $grepScript = preg_quote($script);
        exec("ps -ef | grep '$grepScript'",$output);
        $procIds = array();
        foreach($output as $opKey=>$opItem){
            if (strstr($opItem,"$bin $script")){
                preg_match("/^[^ ]+[ ]+([0-9]+).*$/",$opItem,$pregMatch);
                array_push($procIds,$pregMatch[1]);
            }
        }
        return $procIds;
    }

    /**
     * UNIX/LINUX 下获取正在运行的进程的信息
     *
     * @param string $script
     *            执行的脚本名
     * @param string $bin
     *            命令中的解析器路径, 如 ./php python
     * @return array
     */
    public static function getTaskInfo($script, $bin){
        $grepScript = preg_quote($script);
        exec("ps -ef | grep '$grepScript'",$output);
        $countProc = 0;
        $taskInfo = array(
                'count'=>0,
                'pid'=>array(),
                'cid'=>array()
        );
        foreach($output as $opKey=>$opItem){
            if (strstr($opItem,"$bin $script")){
                preg_match("/^[^ ]+\s+(\d+).*cid\=(\d+)$/",$opItem,$pregMatch);
                if (isset($pregMatch[1])) $taskInfo['pid'][] = $pregMatch[1];
                if (isset($pregMatch[2])) $taskInfo['cid'][] = $pregMatch[2];
                $taskInfo['count']++;
            }
        }
        return $taskInfo;
    }

    /**
     * UNIX/LINUX 下获取正在运行的进程数量
     *
     * @param string $script
     *            执行的脚本名
     * @param string $bin
     *            命令中的解析器路径, 如 ./php python
     * @return integer
     */
    public static function getTaskCount($script, $bin){
        $grepScript = preg_quote($script);
        exec("ps -ef | grep '$grepScript'",$output);
        $countProc = 0;
        foreach($output as $opKey=>$opItem){
            if (strstr($opItem,"$bin $script")) $countProc++;
        }
        return $countProc;
    }

    /**
     * 分析任务执行时间
     *
     * @param string $execTime
     *            任务执行时间
     * @return array
     */
    public static function paserExecTime($execTime){
        $execTime = Ext_Array::serialToArray($execTime);
        foreach($execTime as $key=>$value){
            $execTime[$key] = explode(',',$value);
        }
        return $execTime;
    }

    /**
     * 检测是否到达执行时间
     *
     * @param mixed $nowTime
     *            当前时间
     * @param mixed $execTime
     *            执行时间
     * @return Boolean
     */
    public static function checkExecTime($nowTime, $execTime){
        $checked = true;
        foreach($nowTime as $key=>$val){
            if (isset($execTime[$key]) && !in_array($val,$execTime[$key])){
                $checked = false;
            }
        }
        return $checked;
    }

    /**
     * 启动主控进程
     *
     * @param array $taskConfig
     *            进程控制参数
     * @param object $db
     *            进程控制表所在的数据库对象引用
     * @return void
     */
    public static function start($taskConfig, & $db){
        $phpBin = $taskConfig['php_bin'];
        $waitTime = $taskConfig['wait_time'];
        $taskControlTbale = $taskConfig['table'];
        $logPath = Wee::$config['data_path'] . 'log/task_log/';
        
        // 检查主控运行状态
        if ($_SERVER['argc'] > 1){
            exit('I do not need parameters...' . chr(10));
        }
        $mainScript = $_SERVER['PHP_SELF'];
        $mainInfo = Ext_Task::getTaskInfo($mainScript,$phpBin);
        $mainCount = $mainInfo['count'];
        if ($mainCount > 1){
            exit('I am Already Run...' . chr(10));
        }
        
        // 监控任务进程
        while(true){
            $startUsec = microtime(true);
            $time = time();
            $nowTime = Ext_Date::getInfo($time);
            
            // 获取未暂停的进程列表
            $taskList = $db->table($taskControlTbale)->getAll('id');
            foreach($taskList as $taskId=>$taskControl){
                $maxCount = max(1,$taskControl['max_count']);
                $taskInfo = Ext_Task::getTaskInfo($taskControl['script'],
                        $phpBin);
                $taskCount = $taskInfo['count'];
                
                // 暂停状态
                if ($taskControl['status'] < 0){
                    if ($taskCount > 0){
                        $pids = Ext_Task::getTaskId($taskControl['script'],
                                $phpBin);
                        foreach($taskInfo['pid'] as $pid){
                            $cmd = "kill -9 $pid";
                            echo '[' . Ext_Date::format($time) . ']' . $cmd .
                                     chr(10);
                            exec($cmd);
                        }
                    }
                    continue;
                }
                
                // 子进程已经存在
                if ($taskCount >= $maxCount){
                    continue;
                }
                
                // 定时执行的进程验证执行时间
                if ('keep' != $taskControl['type']){
                    // 每次执行时间间隔
                    if ('minute' == $taskControl['type']){
                        $timePart = 60;
                    }elseif ('hour' == $taskControl['type']){
                        $timePart = 3600;
                    }else{
                        $timePart = 86400;
                    }
                    // 当前时间已经执行过
                    if ($time <
                             strtotime($taskControl['last_exec_time']) +
                             $timePart){
                        continue;
                    }
                    // 未满足执行时间
                    $checked = Ext_String::formula($taskControl['exec_time'],
                            $nowTime);
                    if (false == $checked){
                        continue;
                    }
                }
                
                // 更新进程最后执行时间
                $timeStr = Ext_Date::format($time);
                $db->begin();
                $db->table($taskControlTbale)
                    ->where(array(
                        'id'=>$taskId
                ))
                    ->update(array(
                        'last_exec_time'=>$timeStr,
                        'status'=>1
                ));
                $db->commit();
                
                // 启动进程
                $logFile = Logs::getLogFile('task_' . $taskId,$time);
                $cids = array_diff(range(1,$maxCount),$taskInfo['cid']);
                foreach($cids as $cid){
                    $cmd = "$phpBin {$taskControl['script']} cid=$cid >> $logFile &";
                    echo '[' . Ext_Date::format($time) . ']' . $cmd . chr(10);
                    exec($cmd);
                }
            }
            $endUsec = microtime(true);
            $waitUsec = 1000000 * ($waitTime - ($endUsec - $startUsec));
            if ($waitUsec > 0){
                usleep($waitUsec);
            }
        }
    }
}