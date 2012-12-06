<?php
/*
 * Copyright (c) 2008-2012 PicCMS.Com All rights reserved. This is NOT a
 * freeware, use is subject to license terms $Author: 小鱼哥哥 <29500196@qq.com>
 * <QQ:29500196> $ $Time: 2011-12-27 17:14 $
 */

if (!defined('APP_PATH')){
    exit("APP_PATH undefined");
}

// 定义框架路径
define('CORE_PATH',rtrim(dirname(__FILE__),'/\\') . DIRECTORY_SEPARATOR);

// 加载核心类库
require_once 'class/Wee.php';

// 加载核心函数库
require_once 'common/funs.php';

// 初始化运行时信息
Wee::$box['_runStartTime'] = microtime(true);

// 自动加载对象
spl_autoload_register('__autoload_class');

// 注册shutdow事件
register_shutdown_function('__shutdown');

// 载入系统配置文件
require CORE_PATH . 'common/config.php';

// 载入项目配置
if (is_file(APP_PATH . 'data/config.php')){
    require APP_PATH . 'data/config.php';
}

// 路由规则
if (Wee::$config['url_route']){
    if (Wee::$config['url_route_rule']){
        foreach(Wee::$config['url_route_rule'] as $key=>$value){
            Wee::$config['url_route_reverse'][$value[0] . '-' . $value[1]] = $key;
        }
    }
}

// 错误和异常处理
if (Wee::$config['debug_mode']){
    // Wee::$config['error_types'] = E_ALL & ~E_NOTICE;
}else{
    Wee::$config['error_types'] = 0;
}
error_reporting(Wee::$config['error_types']);

if (Wee::$config['error_exception']){
    set_error_handler('__error_handler',Wee::$config['error_types']);
}
set_exception_handler('catch_error');

// 设置系统时间
if (function_exists('date_default_timezone_set')){
    date_default_timezone_set(Wee::$config['default_timezone']);
}

// 表单自动保存
if (Wee::$config['form_auto_cache']){
    header('Cache-Control: private,must-revalidate');
    session_cache_limiter('private,must-revalidate');
}

// 自动启用Session
if (Wee::$config['session_auto_start']){
    Session::start();
}

// 默认编码
header("Content-type: text/html; charset=" . Wee::$config['charset']);

// 开启输出缓冲
ob_start();

