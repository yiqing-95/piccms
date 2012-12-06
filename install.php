<?php
/*
 * Copyright (c) 2008-2012 PicCMS.Com All rights reserved. This is NOT a
 * freeware, use is subject to license terms $Author: 小鱼哥哥 <29500196@qq.com>
 * <QQ:29500196> $ $Time: 2011-12-27 17:23 $
 */

// 应用程序路径
define('APP_PATH',rtrim(dirname(__FILE__),'/\\') . DIRECTORY_SEPARATOR);

// 加载框加入口
require_once './core/loader.php';

Wee::$config['entrance'] = Wee::ENTRANCE_INSTALL;

class Install_Controller extends Controller {

    public function __construct(){
        parent::__construct();
        $this->lockFile = Wee::$config['data_path'] . 'install/install.lock';
        if (is_file($this->lockFile)){
            show_msg('系统已经安装过, 如果确认要重新安装, 请删除 data/install/install.lock 文件',null,-1);
        }
        if (!$this->_isWritable('/data/')){
            show_msg('data/ 目录不可写, 请将 data/ 目录权限设置为 0777',null,-1);
        }
        
        $this->output->set(
                array(
                        'sys_name'=>Wee::$config['sys_name'],
                        'sys_url'=>Wee::$config['sys_url'],
                        'sys_ver'=>Wee::$config['sys_ver'],
                        'web_uri'=>Wee::$config['web_uri'],
                        'web_dir'=>Wee::$config['web_dir'],
                        'web_script'=>Wee::$config['web_script']
                ));
    }

    public function index(){
        $this->output->display('setup-1.html');
    }

    public function setup2(){
        $gd_ver = 0;
        if (function_exists('gd_info')){
            $gd = gd_info();
            $gd_ver = $gd['GD Version'];
        }
        $writeDir = array(
                '/',
                '/data/',
                '/attach/'
        );
        $dirStatus = array();
        foreach($writeDir as $value){
            $dirStatus[$value] = array(
                    'read'=>$this->_isReadable($value),
                    'write'=>$this->_isWritable($value)
            );
        }
        $this->output->set(
                array(
                        'allow_url_fopen'=>ini_get('allow_url_fopen'),
                        'not_safe_mode'=>!ini_get('safe_mode'),
                        'gd_ver'=>$gd_ver,
                        'mysql'=>function_exists('mysql_connect'),
                        'dirStatus'=>$dirStatus
                ));
        $this->output->display('setup-2.html');
    }

    public function setup3(){
        $this->output->display('setup-3.html');
    }

    public function setup4(){
        $con = $this->input->get('con');
        $this->db = new Db_Mysql($con['host'],$con['port'],$con['user'],$con['pass']);
        try{
            $this->db->connect();
        }catch(Error $e){
            show_msg("数据库配置失败, 请检查所填参数是否正确<br>" . $e->getMessage());
        }
        try{
            $this->db->useDb($con['dbname']);
        }catch(Error $e){
            $this->_createDatebase($con['dbname']);
        }
        try{
            $this->db->useDb($con['dbname']);
        }catch(Error $e){
            show_msg("建立数据库 " . $con['dbname'] . " 失败, 请检查数据库权限");
        }
        $dbPlus = new Db_Plus($this->db);
        $file = Wee::$config['view_path'] . 'install/setup.sql';
        if (!$file){
            show_msg("$file: SQL文件不存在");
        }
        $sql = Ext_File::read($file);
        $sql = str_replace("`#@_","`{$con['db_table_prefix']}",$sql);
        $dbPlus->batQuery($sql);
        
        $data = array(
                'web_path'=>$con['web_path'],
                'web_url'=>Wee::$config['web_host'] . $con['web_path']
        );
        Wee::$config['db_table_prefix'] = $con['db_table_prefix'];
        foreach($data as $name=>$value){
            $this->db->table('#@_config')->replace(
                    array(
                            'name'=>$name,
                            'value'=>$value
                    ));
        }
        $config = $this->db->table('#@_config')->getAll();
        $config = Ext_Array::format($config,'name','value');
        write_config(Wee::$config['data_path'] . 'web-config.php',$config);
        
        $encrypt_key = Ext_String::getSalt(32);
        $str = "<?php
// ----------------------------------------
Wee::\$config['db_config_main'] = array(
	'host' => '{$con['host']}', 
	'port' => '{$con['port']}',	
	'user' => '{$con['user']}',       
	'pass' => '{$con['pass']}',      
	'dbname' => '{$con['dbname']}',      
);
// ----------------------------------------
Wee::\$config['db_table_prefix'] = '{$con['db_table_prefix']}';
// ----------------------------------------
Wee::\$config['debug_mode'] = true;
Wee::\$config['template_debug'] = false;
Wee::\$config['encrypt_key'] = '{$encrypt_key}';
// ----------------------------------------
if (is_file(Wee::\$config['data_path'] . 'web-config.php')) {
	require_once Wee::\$config['data_path'] . 'web-config.php';
}";
        Ext_File::write(Wee::$config['data_path'] . 'config.php',$str);
        Ext_File::write($this->lockFile,' ');
        $this->output->display('setup-4.html');
    }

    private function _isWritable($dir){
        $dir = trim($dir,"\\/");
        if ($dir){
            $testfile = APP_PATH . "$dir/piccms.test";
        }else{
            $testfile = APP_PATH . "piccms.test";
        }
        if (@Ext_File::write($testfile,' ')){
            if (@unlink($testfile)){
                return true;
            }
        }
        return false;
    }

    private function _isReadable($dir){
        $dir = trim($dir,"\\/");
        if ($dir){
            $testfile = APP_PATH . "$dir/";
        }else{
            $testfile = APP_PATH;
        }
        return is_readable($testfile);
    }

    private function _createDatebase($name){
        $sql = "CREATE DATABASE `$name` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
        $rs = $this->db->query($sql);
        return $rs;
    }
}

Wee::init();
try{
    $actionName = Wee::$input->getActionName();
    $handle = new Install_Controller();
    $handle->$actionName();
}catch(Error $e){
    catch_error($e);
}