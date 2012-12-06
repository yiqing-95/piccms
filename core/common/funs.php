<?php
/*
 * Copyright (c) 2008-2012 PicCMS.Com All rights reserved. This is NOT a
 * freeware, use is subject to license terms $Author: 小鱼哥哥 <29500196@qq.com>
 * <QQ:29500196> $ $Time: 2011-12-27 17:21 $
 */

/**
 * 自动加载类库
 *
 * @param string $className
 *            类名
 * @return void
 */
function __autoload_class($className){
    if (class_exists($className,false)){
        return true;
    }
    $tmpArr = explode('_',$className);
    $suffix = null;
    if (count($tmpArr) > 1){
        $suffix = end($tmpArr);
    }
    if ('Controller' == $suffix){
        array_pop($tmpArr);
        $classFile = Wee::$config['controller_path'] . implode('/',$tmpArr) . '.php';
    }else 
        if ('Model' == $suffix){
            array_pop($tmpArr);
            $classFile = Wee::$config['model_path'] . implode('/',$tmpArr) . '.php';
        }else{
            $classFile = CORE_PATH . 'class/' . strtr($className,
                    array(
                            '_'=>'/'
                    )) . '.php';
        }
    import_file($classFile,false);
    return class_exists($className,false);
}

/**
 * shutdown事件回调函数
 *
 * @return void
 */
function __shutdown(){}

/**
 * 默认的错误处理
 *
 * @param integer $errno
 *            错误类型
 * @param string $errmsg
 *            错误消息
 * @param string $errfile
 *            错误文件
 * @param string $errline
 *            错误行
 * @return void
 */
function __error_handler($errno, $errmsg, $errfile, $errline){
    if (!(error_reporting() & $errno)){
        return;
    }
    if (isset(Wee::$output)){
        Wee::$output->setState(-1);
        Wee::$output->set('errorMsg',$errmsg);
    }
    throw new Error($errmsg,Error::PHP_ERROR);
}

/**
 * 处理错误信息
 *
 * @param object $e
 *            异常对象
 * @return void
 */
function catch_error($e){
    $error = $e->getError();
    if (Error::USER_MSG == $error['code']){
        return;
    }
    
    require CORE_PATH . '/misc/show_error.php';
    /*
     * if (Wee::$config['debug_mode']) { require CORE_PATH .
     * '/misc/show_error.php'; } else { show_msg("{$error['type']}:
     * {$error['message']} [File:{$error['file']} Line:{$error['line']}]"); }
     */
    
    // 纪录数据库错误日志
    if (Error::DB_ERROR == $error['code'] && Wee::$config['error_db_log']){
        $logCon = "{$error['type']}: {$error['message']}\n" . implode("\n",$error['trace']);
        Logs::errorDbLog($logCon);
    }
    
    // 纪录程序错误日志
    if (Wee::$config['error_code_log'] && (Error::CODE_ERROR == $error['code'] || Error::PHP_ERROR == $error['code'])){
        $logCon = "{$error['type']}: {$error['message']}\n" . implode("\n",$error['trace']);
        Logs::errorCodeLog($logCon);
    }
}

/**
 * 获取或者设定配置参数
 *
 * @param
 *            mixed 参数名或者参数值数组
 * @param
 *            mixed 参数值
 * @return void
 */
function set_config($name, $value = null){
    if (is_array($name)){
        Wee::$config = array_merge(Wee::$config,$name);
    }else{
        if (is_null($value)){
            return Wee::$config[$name];
        }else{
            Wee::$config[$name] = $value;
        }
    }
}

/**
 * 获取或者设定配置参数
 *
 * @param mixed $file
 *            要保存的文件路径
 * @param mixed $data
 *            参数数组
 * @return void
 */
function write_config($file, $data){
    $arr = array();
    foreach($data as $key=>$value){
        $arr[] = "Wee::\$config['$key'] = " . var_export($value,true) . ';';
    }
    $content = "<?php\n" . implode("\n",$arr);
    $rs = Ext_File::write($file,$content);
}

/**
 * 调试一个变量
 *
 * @param mixed $vars
 *            变量
 * @param
 *            mixed .... 更多的变量
 * @param string $title
 *            标题
 * @return void
 */
function dump($vars){
    $varsArr = func_get_args();
    if (count($varsArr) > 1){
        $vars = $varsArr;
    }
    $content = (print_r($vars,true));
    $content = "<fieldset><pre>" . $content . "</pre></fieldset>\n";
    echo $content;
}

/**
 * 生成请求地址
 *
 * @param mixed $args            
 * @return mixed
 */
function url($module = '', $action = '', $args = array(), $mode = null){
    if (is_null($mode)){
        $mode = Wee::$config['url_mode'];
    }
    if (0 == $mode){
        $tmpArr = array();
        if ($module){
            $tmpArr[Wee::$config['controller_var_name']] = $module;
        }
        if ($action){
            $tmpArr[Wee::$config['action_var_name']] = $action;
        }
        $tmpArr = array_merge($tmpArr,$args);
        if ($tmpArr){
            $url = Wee::$config['url_index'] . '?' . http_build_query($tmpArr);
        }else{
            $url = '';
        }
        return Wee::$config['web_url'] . $url;
    }
    if (1 == $mode || 2 == $mode){
        if (!$module){
            $module = Wee::$config['default_controller'];
        }
        if (!$action){
            $action = Wee::$config['default_action'];
        }
        if (Wee::$config['url_route'] && isset(Wee::$config['url_route_reverse'][$module . '-' . $action])){
            $routeName = Wee::$config['url_route_reverse'][$module . '-' . $action];
            $tmpArr = array(
                    $routeName
            );
            foreach($args as $value){
                $tmpArr[] = urlencode($value);
            }
        }else{
            $tmpArr = array(
                    $module,
                    $action
            );
            foreach($args as $key=>$value){
                $tmpArr[] = $key;
                $tmpArr[] = urlencode($value);
            }
        }
        $url = implode(Wee::$config['url_delimiter'],$tmpArr) . Wee::$config['url_suffix'];
        if (1 == $mode){
            return Wee::$config['web_url'] . Wee::$config['url_index'] . '?' . $url;
        }else{
            return Wee::$config['web_url'] . $url;
        }
    }
}

/**
 * 检查当前请求是否为数据提交
 *
 * @param
 *            mixed
 * @return void
 */
function check_submit($name = 'submit'){
    return !empty($_POST[$name]);
}

/**
 * 判断 HTTP 请求是否是通过 XMLHttp 发起的
 *
 * @return boolean
 */
function inajax(){
    if (!empty($_REQUEST['inajax'])){
        return true;
    }
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
        return true;
    }
    return false;
}

/**
 * 加载模型实例
 *
 * @param string $modelName
 *            模型名
 * @param bool $single
 *            是否单例模式
 * @return User_Model
 */
function load_model($modelName, $single = true){
    $className = $modelName . '_Model';
    if (!$single){
        return new $className();
    }
    if (!isset(Wee::$box['ModelInstance'][$className])){
        Wee::$box['ModelInstance'][$className] = new $className();
    }
    return Wee::$box['ModelInstance'][$className];
}

/**
 * 显示程序错误
 *
 * @param string $errmsg
 *            错误信息
 * @param integer $errcode
 *            错误类型代码
 * @return void
 */
function show_error($errorMsg){
    if (Wee::$output){
        Wee::$output->setState(-1);
        Wee::$output->set('errorMsg',$errorMsg);
    }
    throw new Error($errorMsg,Error::CODE_ERROR);
}

/**
 * 抛出用户提示信息
 *
 * @param string $reportMsg
 *            用户消息
 * @param string $url
 *            要调转到的地址
 * @return void
 */
function show_msg($errorMsg, $url = null, $refresh = 3, $backUrl = "javascript:history.go(-1)"){
    if (isset(Wee::$output)){
        Wee::$output->setState(-2);
        Wee::$output->set('errorMsg',$errorMsg);
    }
    if ($url && 0 == $refresh){
        ob_end_clean();
        header("Location: $url");
    }
    if (!$url){
        $url = $backUrl;
    }
    $inajax = Wee::$input->get('inajax');
    $inframe = Wee::$input->get('inframe');
    if (Wee::$config['show_msg_tpl']){
        require Wee::$config['show_msg_tpl'];
    }else{
        require CORE_PATH . '/misc/show_msg.php';
    }
    throw new Error($errorMsg,Error::USER_MSG);
}

/**
 * 执行JS方法
 *
 * @param
 *            mixed
 * @return void
 */
function js_run($js){
    echo "<script>$js</script>";
    ob_flush();
    flush();
}

/**
 * 加载文件
 *
 * @param string $fileName
 *            文件名
 * @param string $blackout
 *            文件不存在时退出
 * @return bool
 */
function import_file($fileName, $blackout = false){
    if (!isset(Wee::$box['importFiles'][$fileName])){
        if (is_file($fileName)){
            require $fileName;
        }else{
            if ($blackout){
                exit("$fileName: File not exists");
            }
            return false;
        }
        Wee::$box['importFiles'][$fileName] = true;
    }
    return true;
}

/**
 * 获取缓存对象
 *
 * @return object 缓存对象
 */
function load_cache(){
    if (!isset(Wee::$box['CacheInstance'])){
        $obj = new Cache();
        Wee::$box['CacheInstance'] = $obj;
    }
    return Wee::$box['CacheInstance'];
}

/**
 * 初使化连接
 *
 * @param string $tag
 *            数据库连接标识
 * @return Db 数据库连接对象
 */
function load_db($tag = 'main'){
    if (!isset(Wee::$box['DbInstance'][$tag])){
        $cfgkey = 'db_config_' . $tag;
        if (isset(Wee::$config[$cfgkey])){
            $dbCfg = Wee::$config[$cfgkey];
        }else{
            exit("$tag: The dbtag does not exist");
        }
        if ('Db_Mysql' == Wee::$config['db_driver']){
            $driverName = 'Db_Mysql';
        }else 
            if ('Db_Mysqli' == Wee::$config['db_driver']){
                $driverName = 'Db_Mysqli';
            }else{
                exit(Wee::$config['db_driver'] . ": The DB driver does not exist");
            }
        $db = new $driverName($dbCfg['host'],$dbCfg['port'],$dbCfg['user'],$dbCfg['pass'],$dbCfg['dbname']);
        Wee::$box['DbInstance'][$tag] = $db;
    }
    return Wee::$box['DbInstance'][$tag];
}

/**
 * 获取程序运行时信息
 *
 * @param
 *            mixed
 * @return void
 */
function get_runtime($more = true){
    Wee::$box['_runEndTime'] = microtime(true);
    Wee::$box['_runTime'] = round(Wee::$box['_runEndTime'] - Wee::$box['_runStartTime'],4);
    if ($more){
        $data = array(
                'startTime'=>Wee::$box['_runStartTime'],
                'endTime'=>Wee::$box['_runEndTime'],
                'runTime'=>Wee::$box['_runTime'],
                'sqlQueryNum'=>0,
                'sqlQueryTime'=>0
        );
        if (isset(Wee::$box['sqlQuery'])){
            $data['sqlQueryNum'] = count(Wee::$box['sqlQuery']);
            foreach(Wee::$box['sqlQuery'] as $value){
                $data['sqlQueryTime'] += $value['runTime'];
            }
        }
        return $data;
    }else{
        return Wee::$box['_runTime'];
    }
}
if(!function_exists('in_str')){
    /**
     * to fix the error of issue 11  dolfly<dolfly@foxmail.com
     * @param string $needle
     * @param string $haystack
     * @return boolean
     */
    function in_str($needle , $haystack){
        if(FALSE === strpos($haystack, $needle)){
            return FALSE;
        }
        return TRUE;
    }
}

