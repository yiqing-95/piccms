<?php
/*
 * Copyright (c) 2008-2012 PicCMS.Com All rights reserved. This is NOT a
 * freeware, use is subject to license terms $Author: 小鱼哥哥 <29500196@qq.com>
 * <QQ:29500196> $ $Time: 2011-12-27 17:23 $
 */

// 应用程序路径
define('APP_PATH',rtrim(dirname(__FILE__),'/\\') . DIRECTORY_SEPARATOR);

if (!is_file(APP_PATH . 'data/config.php')){
    header('Location: install.php');
}

// 加载框加入口
require_once './core/loader.php';

// 运行程序
Wee::run();
