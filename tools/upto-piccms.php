<?php
/*
 * Copyright (c) 2008-2012 PicCMS.Com All rights reserved. This is NOT a
 * freeware, use is subject to license terms $Author: 小鱼哥哥 <29500196@qq.com>
 * <QQ:29500196> $ $Time: 2011-12-27 17:23 $
 */

// 是否显示详细转换信息, 数据多时建议关闭
$showAll = false;

set_time_limit(0);
// 应用程序路径
define('APP_PATH',
        rtrim(dirname(dirname(__FILE__)),'/\\') . DIRECTORY_SEPARATOR);

// 加载框加入口
require_once '../core/loader.php';

Wee::init();
if (check_submit()){
    $data = Wee::$input->get('data');
    $stepNum = Wee::$input->get('step_num');
    if ($stepNum < 1){
        $stepNum = 100;
    }
    $dbPrefix = $data['db_prefix'];
    $db = new Db_Mysql($data['db_host'],$data['db_port'],$data['db_user'],
            $data['db_pwd'],$data['db_name']);
    try{
        $db->connect();
    }catch(Error $e){
        show_msg("数据库配置失败, 请检查所填参数是否正确<br>" . $e->getMessage());
    }
    
    // 分类
    printMsg("开始转换分类...");
    $dbMain = load_db();
    $cate = $db->table("{$dbPrefix}cate")->getAll();
    $data = array();
    foreach($cate as $value){
        $data[] = array(
                'cid'=>$value['cid'],
                'pid'=>0,
                'name'=>$value['name'],
                'oid'=>$value['turn'],
                'cdescription'=>Ext_String::cut($value['des'],200)
        );
    }
    $dbMain->query("TRUNCATE TABLE " . Wee::$config['db_table_prefix'] . "cate");
    $dbMain->table('#@_cate')->insert($data);
    printMsg(" =*= 分类转换完成 =*= ");
    
    // 文章
    $total = $db->field("COUNT(1) AS num")
        ->table("{$dbPrefix}album")
        ->getOne();
    $total = $total['num'];
    printMsg("开始转换文章, 共 $total 篇...");
    $start = 0;
    $dbMain->query(
            "TRUNCATE TABLE " . Wee::$config['db_table_prefix'] . "article");
    $dbMain->query("TRUNCATE TABLE " . Wee::$config['db_table_prefix'] . "tags");
    while($start < $total){
        $list = $db->table("{$dbPrefix}album")
            ->limit("$start, $stepNum")
            ->getAll();
        foreach($list as $value){
            $data = array(
                    'id'=>$value['id'],
                    'cid'=>$value['cid'],
                    'cover'=>$value['mini'],
                    'title'=>addslashes($value['title']),
                    'tag'=>$value['tag'],
                    'content'=>addslashes($value['content']),
                    'remark'=>addslashes(
                            Ext_String::cut(strip_tags($value['content']),200)),
                    'addtime'=>$value['addtime'],
                    'up'=>$value['up'],
                    'down'=>$value['down'],
                    'hits'=>$value['views'],
                    'status'=>$value['isok']
            );
            if (3 == $value['hot']){
                $data['star'] = 5;
            }elseif ($value['hot']){
                $data['star'] = $value['hot'];
            }else{
                $data['star'] = 1;
            }
            $dbMain->table('#@_article')->insert($data);
            if ($data['tag']){
                $tagArr = explode(",",$data['tag']);
                $data = array();
                foreach($tagArr as $val){
                    $data[] = array(
                            'tag'=>$val,
                            'title'=>$value['title'],
                            'article_id'=>$value['id']
                    );
                }
                $dbMain->table('#@_tags')->insert($data);
            }
            if ($showAll){
                printMsg("<small>Done! {$value['title']}</small>");
            }
        }
        $start += $stepNum;
        printMsg(" =*= " . min($start,$total) . " 篇文章转换完成 =*= ");
    }
    
    // 附件
    $total = $db->field("COUNT(1) AS num")
        ->table("{$dbPrefix}pics")
        ->getOne();
    $total = $total['num'];
    printMsg("开始转换附件, 共 $total 个...");
    $start = 0;
    $dbMain->query(
            "TRUNCATE TABLE " . Wee::$config['db_table_prefix'] . "attach");
    while($start < $total){
        $list = $db->table("{$dbPrefix}pics")
            ->limit("$start, $stepNum")
            ->getAll();
        foreach($list as $value){
            $data = array(
                    'id'=>$value['picid'],
                    'article_id'=>$value['albumid'],
                    'uid'=>0,
                    'name'=>addslashes($value['name']),
                    'remark'=>addslashes($value['text']),
                    'size'=>$value['size'],
                    'file'=>$value['file'],
                    'ext'=>$value['ext'],
                    'status'=>1,
                    'type'=>(0 === strpos($value['file'],'http://')) ? 1:0,
                    'upload_time'=>$value['dateline']
            );
            $dbMain->table('#@_attach')->insert($data);
            if ($showAll){
                printMsg("<small>Done! {$value['file']}</small>");
            }
        }
        $start += $stepNum;
        printMsg(" =*= " . min($start,$total) . " 个附件转换完成 =*= ");
    }
    printMsg("恭喜您! 转换完成!");
    exit();
}

function printMsg($msg){
    echo "$msg<br>";
    ob_flush();
    flush();
}
?>
<html>
<head>
<title>Mypic2.2 to PicCMS1.0 转换程式</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
	<table width="600" border="0" cellpadding="4" cellspacing="1"
		class="tableoutline" bgcolor="#999999">
		<tr nowrap bgcolor="#CCCCCC" class="tb_head">
			<td colspan="4"><b>Mypic2.2 to PicCMS1.0 转换说明：</b></td>
		</tr>
		<tr bgcolor="#FFFFFF" class="firstalt">
			<td colspan="4">1. 全新安装PicCMS 1.0</td>
		</tr>
		<tr bgcolor="#FFFFFF" class="firstalt">
			<td colspan="4">2. 上传本程式至 PicCMS目录/tools/ 下, 运行本程式</td>
		</tr>
		<tr bgcolor="#FFFFFF" class="firstalt">
			<td colspan="4">3. 设置Mypic的数据库参数, 开始转换数据</td>
		</tr>
		<tr bgcolor="#FFFFFF" class="firstalt">
			<td colspan="4">4. 用图片处理工具批量生成附件缩略图, 缩略图命名方式: [原文件名]_s.[扩展名]<br> 如:
				2011-11/1322201022vnH4.jpg 对应的缩略图为 2011-11/1322201022vnH4_s.jpg<br>
				<span style="color: red">也可以在完成第5步后使用 <a href="make-thumb.php"
					target="_blank">重建缩略图</a> 工具
			</span></td>
		</tr>
		<tr bgcolor="#FFFFFF" class="firstalt">
			<td colspan="4">5. 转移所有附件到PicCMS附件目录下, 并到后台进行相应设置</td>
		</tr>
		<tr bgcolor="#FFFFFF" class="firstalt">
			<td colspan="4">6. <span style="color: red">更新缓存</span>, 转换完成
			</td>
		</tr>
		<tr bgcolor="#FFFFFF" class="firstalt">
			<td colspan="4">注: 一级分类不会转换, 请手工添加, 转换完成请删除本程式</td>
		</tr>
	</table>
	<form action="upto.php" method="post" name="setting">
		<table width="600" border="0" cellpadding="4" cellspacing="1"
			class="tableoutline" bgcolor="#999999">

			<tr nowrap bgcolor="#CCCCCC" class="tb_head">
				<td colspan="2"><b>Mypic数据库设置：</b></td>
			</tr>
			<tr bgcolor="#FFFFFF" class="firstalt">
				<td width="48%">服务器地址</td>
				<td><input type="text" name="data[db_host]" size="35" maxlength="50"
					value="localhost" id="data[db_host]" valid="required"
					errmsg="服务器地址不能为空!"></td>
			</tr>
			<tr bgcolor="#FFFFFF" class="firstalt">
				<td width="48%">服务器端口</td>
				<td><input type="text" name="data[db_port]" size="35" maxlength="50"
					value="3306" id="data[db_port]" valid="required"
					errmsg="服务器地址不能为空!"></td>
			</tr>
			<tr nowrap bgcolor="#FFFFFF" class="firstalt">
				<td width="48%">数据库名</td>
				<td><input name="data[db_name]" type="text" id="data[db_name]"
					value="mypic" size="35" maxlength="50"></td>
			</tr>
			<tr nowrap bgcolor="#FFFFFF" class="firstalt">
				<td width="48%">数据库用户名</td>
				<td><input name="data[db_user]" type="text" id="data[db_user]"
					value="root" size="35" maxlength="50"></td>
			</tr>
			<tr nowrap bgcolor="#FFFFFF" class="firstalt">
				<td width="48%">数据库密码</td>
				<td><input name="data[db_pwd]" type="text" id="data[db_pwd]"
					size="35" maxlength="50"></td>
			</tr>
			<tr nowrap bgcolor="#FFFFFF" class="firstalt">
				<td width="48%">表前缀</td>
				<td><input name="data[db_prefix]" type="text" id="data[db_prefix]"
					value="mp_" size="35"></td>
			</tr>
			<tr nowrap bgcolor="#FFFFFF" class="firstalt">
				<td width="48%">第次处理多少条数据</td>
				<td><input name="step_num" type="text" id="step_num" value="100"
					size="35" maxlength="50"></td>
			</tr>
			<tr bgcolor="#FFFFFF" class="firstalt">
				<td colspan="2" align="center"><input class="bginput" type="submit"
					name="submit" value=" 开始转换 "></td>
			</tr>
		</table>
	</form>

</html>
