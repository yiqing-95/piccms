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
if (Wee::$input->get('submit')){
    $remake = Wee::$input->get('remake');
    $articleId = Wee::$input->get('article_id');
    $uploadTime = Wee::$input->get('upload_time');
    $stepNum = Wee::$input->get('step_num');
    $start = Wee::$input->getIntval('start');
    if ($stepNum < 1){
        $stepNum = 100;
    }
    $urlArr = array(
            'remake'=>$remake,
            'submit'=>true,
            'article_id'=>$articleId,
            'upload_time'=>$uploadTime,
            'step_num'=>$stepNum
    );
    $where = array();
    if ($uploadTime){
        $where[] = "upload_time > " . strtotime($uploadTime);
    }
    if ($articleId){
        $where['article_id'] = explode(",",$articleId);
    }
    $modAttach = load_model('Attach');
    $total = $modAttach->getTotalNum($where);
    
    $list = $modAttach->search($where,"$start, $stepNum");
    if ($list){
        foreach($list as $value){
            $thumbPath = $modAttach->getAttachPath($value['thumb_file']);
            $attachPath = $modAttach->getAttachPath($value['file']);
            if (!is_file($thumbPath) || $remake){
                if (is_file($attachPath)){
                    // 生成缩略图
                    Ext_Image::cut($attachPath,$thumbPath,
                            Wee::$config['upload_thumb_w'],
                            Wee::$config['upload_thumb_h'],
                            Wee::$config['upload_thumb_type'],
                            Wee::$config['upload_cut_pct']);
                }else{
                    printMsg(
                            "<small>[ID:{$value['id']}] {$value['file']}: 原文件不存在</small>");
                }
            }
        }
        $start += $stepNum;
        $urlArr['start'] = $start;
        $url = "make-thumb.php?" . http_build_query($urlArr);
        show_msg(min($start,$total) . "/$total 个附件处理完成, 请稍后...",$url,1);
    }else{
        show_msg("转换完成","make-thumb.php",3);
    }
    exit();
}

function printMsg($msg){
    echo "$msg<br>";
    ob_flush();
    flush();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PicCMS 缩略图重建工具</title>
</head>

<body>
	<table width="600" border="0" cellpadding="4" cellspacing="1"
		class="tableoutline" bgcolor="#999999">
		<tr nowrap="nowrap" bgcolor="#CCCCCC" class="tb_head">
			<td><b>PicCMS 缩略图重建工具</b></td>
		</tr>
		<tr bgcolor="#FFFFFF" class="firstalt">
			<td valign="top">1. 本程式将指定条件的图片重新生成缩略图</td>
		</tr>
		<tr bgcolor="#FFFFFF" class="firstalt">
			<td valign="top">2. 消耗资源, 请在系统空闲时运行.</td>
		</tr>
		<tr bgcolor="#FFFFFF" class="firstalt">
			<td valign="top">3. 如果是采集或者转换过来的数据建议用客户端软件生成后上传</td>
		</tr>
		<tr bgcolor="#FFFFFF" class="firstalt">
			<td valign="top">4. 缩略图命名方式: [原文件名]_s.[扩展名]<br /> 如:
				2011-11/1322201022vnH4.jpg 对应的缩略图为 2011-11/1322201022vnH4_s.jpg
			</td>
		</tr>
	</table>
	<form action="make-thumb.php" method="get" name="setting">
		<table width="600" border="0" cellpadding="4" cellspacing="1"
			class="tableoutline" bgcolor="#999999">
			<tr nowrap="nowrap" bgcolor="#CCCCCC" class="tb_head">
				<td colspan="2"><b>筛选条件：</b></td>
			</tr>
			<tr bgcolor="#FFFFFF" class="firstalt">
				<td width="30%" align="right">时间</td>
				<td><input type="text" name="upload_time"
					value="<?php echo date('Y-m-d 00:00:00', time() - 3600 * 24)?>" />
					时间之后</td>
			</tr>
			<tr bgcolor="#FFFFFF" class="firstalt">
				<td align="right">所属文章</td>
				<td><input type="text" name="article_id" value="" /> 多文章用 , 分隔</td>
			</tr>
			<tr bgcolor="#FFFFFF" class="firstalt">
				<td align="right">重建or补充</td>
				<td><input name="remake" type="checkbox" value="1" />
					已经生成过缩略图的图片也重新生成</td>
			</tr>
			<tr bgcolor="#FFFFFF" class="firstalt">
				<td align="right">数量</td>
				<td><input type="text" name="step_num" value="10" /> 每次处理多少张</td>
			</tr>
			<tr bgcolor="#FFFFFF" class="firstalt">
				<td colspan="2" align="center"><input class="bginput" type="submit"
					name="submit" value=" 开始转换 " /></td>
			</tr>
		</table>
	</form>
</body>
</html>

