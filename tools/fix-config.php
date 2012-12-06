<html>
<title>PicCMS fix config</title>
<?php
// 应用程序路径
define('APP_PATH',
        rtrim(dirname(dirname(__FILE__)),'/\\') . DIRECTORY_SEPARATOR);

// 加载框加入口
require_once '../core/loader.php';

Wee::init();
$db = load_db();
$config = $db->table('#@_config')->getAll();
$config = Ext_Array::format($config,'name','value');
$rs = write_config(Wee::$config['data_path'] . 'web-config.php',$config);
echo '重建网站配置文件成功：' . Wee::$config['data_path'] . 'web-config.php';
