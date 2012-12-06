<?php
/*
 * Copyright (c) 2008-2012 PicCMS.Com All rights reserved. This is NOT a
 * freeware, use is subject to license terms $Author: 小鱼哥哥 <29500196@qq.com>
 * <QQ:29500196> $ $Time: 2011-12-27 17:21 $
 */

if (! defined ( 'APP_PATH' )) {
	exit ( "APP_PATH undefined" );
}

/**
 * @var array 基础配置
 */
Wee::$config = array(
		'sys_name' => 'PicCMS', // 主页
		'sys_url' => 'http://www.piccms.com',
		'sys_ver' => '1.1',
		'default_timezone' => 'Asia/Shanghai', // 默认时区
		'charset' => 'utf-8', // 默认编码
		'error_types' => E_ALL, // 错误类型, 参考error_reporting函数
		'debug_mode' => true, // 默认调试模式,上线时设置为false
		'template_debug' => false, // 模板调试模式
		'template_skin' => 'default', // 默认皮肤
		'error_db_log' => false, // 是否纪录数据库错误日志
		'error_code_log' => false, // 是否纪录代码错误日志
		'error_exception' => true, // 是否启用错误异常
		'error_source_line' => 4, // 显示错误信息源码行数
		'show_msg_tpl' => null, // 消息提示模板
		'form_auto_cache' => true, // 表单自动保存
		
		'entrance' => 0, // 入口标识
		'hack_path' => APP_PATH . 'hack/', // 插件目录
		'data_path' => APP_PATH . 'data/', // 数据目录
		'model_path' => APP_PATH . 'model/', // 模块目录
		'controller_path' => APP_PATH . 'controller/', // 控制器目录
		'view_path' => APP_PATH . 'template/', // 视图目录
		'config_path' => APP_PATH . 'config/', // 项目配置文件目录
		'default_controller' => 'Main', // 默认的控制器名称
		'default_action' => 'index', // 默认的动作名称
		'controller_var_name' => 'c', // 请求时使用的控制器变量标识
		'action_var_name' => 'a', // 请求时使用的方法变量标识
		'view_var_name' => 'v', // 请求视图数据类型的标识
			                        
		// Session_Database / Session_Memcache / Session_File
		'session_auto_start' => false, // 自动启用Session
		'session_handle_class' => 'Session_File', // 默认的Session管理类
		'session_db_tag' => 'main', // Session库标识名
		'session_table' => 'session', // Session表名
			                              
		// Cookie配置
		'cookie_prefix' => 'Wee', // Cookie名前缀
		'cookie_domain' => '', // Cookie域
		'encrypt_key' => 'Gi5TzRhUjL7GzAvT', // 加密字串
			                                     
		// 缓存管理
		'cache_auto_start' => true, // 自动启用缓存
		'cache_compress' => false, // 是否启用压缩
		'cache_config' => array (
				'192.168.1.125:11211' 
		), // 主机1 服务器:端口
		'cache_table' => '#@_cache', // 数据库缓存的表名
			                             
		// 数据库配置
		'db_auto_start' => true, // 自动启用数据库
		'db_driver' => 'Db_Mysql', // 驱动类型 Db_Mysql / Db_Mysqli
		'db_charset' => 'utf8', // 编码
		'db_sql_log' => false, // 记录慢查询日志
		'db_slow_sql_time' => 0, // 慢查询时间
		'db_new_link' => true, // 总是打开新的连接
		'db_table_prefix' => 'my_', // 表前缀
		'db_config_main' => array ( // 主库配置, 默认
				'host' => 'localhost', // 地址
				'port' => '3306', // 端口
				'user' => 'root', // 用户名
				'pass' => 'root1', // 密码
				'dbname' => 'game1'  // 库名称
		),
		'db_config_ini' => array ( // 其它库配置
				'host' => 'localhost', // 地址
				'port' => '3306', // 端口
				'user' => 'root', // 用户名
				'pass' => 'root', // 密码
				'dbname' => 'ini',      // 库名称
		),
		'db_name_alias' => array ( // DB库别名
				'game' => 'dev_game',
				'ini' => 'dev_ini',
				'log' => 'dev_log' 
		),
			
		// 语言包
		'lang_type' => 'chs', // 当前使用的语言种类
		'lang_table' => '#@_lang', // 语言包表名
		'lang_cache' => false, // 语言包是否使用缓存
			                       
		// 进程控制参数
		'task_sleep_time' => 1, // 主控监测等待时间, 秒
		'task_php_bin' => './php', // PHP执行路径
		'task_db_tag' => 'main', // 进程控制数据库标识
		'task_table' => 'task_control', // 进程控制表名
		
		'url_delimiter' => '-', // 伪静态URL分隔符
		'url_suffix' => '.html', // 伪静态时URL后缀
		'url_mode' => 0, // URL模式 0:动态 1:伪静态
		'url_rewrite' => 0, // 是否开启重写
		'url_index' => 'index.php', // 入口文件名
		'url_route' => false, // 是否启用路由
		'url_route_rule' => array (),	// URL路由规则
);