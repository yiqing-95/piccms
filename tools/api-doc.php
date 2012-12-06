<?php
/*
 * Copyright (c) 2008-2012 PicCMS.Com All rights reserved. This is NOT a
 * freeware, use is subject to license terms $Author: 小鱼哥哥 <29500196@qq.com>
 * <QQ:29500196> $ $Time: 2012-1-16 14:49 $
 */

define('APP_PATH',
        rtrim(dirname(dirname(__FILE__)),'/\\') . DIRECTORY_SEPARATOR);
require_once '../core/loader.php';

$title = "PicCMS::API";

$classCate = array(
        'Core'=>'Core框架',
        'Controller'=>'Controller控制器',
        'Model'=>'Model模型'
);

$classList['Core'] = getClassList(Ext_Dir::getDirTree(CORE_PATH . 'class/'));
$classList['Controller'] = getClassList(
        Ext_Dir::getDirTree(Wee::$config['controller_path']));
$classList['Model'] = getClassList(
        Ext_Dir::getDirTree(Wee::$config['model_path']));
foreach($classList['Controller'] as & $value){
    $value .= '_Controller';
}
unset($value);
foreach($classList['Model'] as & $value){
    $value .= '_Model';
}
unset($value);
// Wee::dump($classList);
// 根据文件树获取类列表
function getClassList($coreTree){
    $classList = array();
    foreach($coreTree as $key=>$value){
        if (is_array($value)){
            $res = getClassList($value);
            foreach($res as $val){
                $classList[] = $key . '_' . $val;
            }
        }else{
            $classList[] = substr($value,0,-4);
        }
    }
    return $classList;
}

$cate = isset($_GET['cate']) ? $_GET['cate']:'Core';
$tag = isset($_GET['tag']) ? $_GET['tag']:reset($classList[$cate]);

$obj = new Ext_Reflection($tag);
$doc = $obj->parseDoc();

ksort($doc['method']);
$doc['apiList'] = array();
$doc['methodList'] = array();
foreach($doc['method'] as $value){
    if ($value['isApi']){
        $doc['apiList'][] = $value;
    }else{
        $doc['methodList'][] = $value;
    }
}
$doc['method'] = array_merge($doc['apiList'],$doc['methodList']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $title?> - <?php echo $cate?> - <?php echo $tag?></title>
<style type="text/css">
body {
	font-size: 14px;
	color: #000;
	background: #f6e0cd;
	line-height: 20px;
	font-family: 'Courier New', Courier, monospace;
}

ul {
	list-style: none;
	margin: 0;
	padding: 0 0 0 20px;
}

li {
	padding: 0 5px;
}

a {
	color: #24B;
	text-decoration: none
}

h2 {
	height: 30px;
	line-height: 30px;
	font-weight: bold;
	color: #223355;
	text-indent: 1em;
}

.wrapper {
	margin: 0px auto;
	background: #fff;
	border: 1px dotted #666;
}

.nav {
	height: 30px;
	line-height: 30px;
	font-size: 12px;
	background: #000;
	text-align: left
}

.nav a {
	margin: 0px 10px;
	color: #fff
}

.content {
	float: left;
	width: 800px;
	word-wrap: break-word;
	overflow: hidden;
}

.content li li {
	color: #000;
	font-size: 12px;
	font-weight: lighter;
}

p.code {
	color: red;
	margin: 10px 5px;
	font-size: 14px;
	font-weight: 700;
}

p.doc {
	margin: 0px;
	padding: 5px;
	background: #fff;
	border-left: 1px dotted #CCC;
	margin-left: 20px;
}

.menu {
	float: left;
	border-right: 1px dotted #CCC;
	padding-right: 20px;
	width: auto;
}

.api_span {
	font-family: 'Courier New', Courier, monospace;
	font-size: 11px;
	color: yellow;
	background: red;
}

.method_span {
	font-family: 'Courier New', Courier, monospace;
	font-size: 11px;
	color: white;
	background: blue;
}

.on {
	background: #ddd;
	border: 1px dotted #CCC;
}

.on a {
	color: #000
}
</style>
</head>
<body>
	<div class="wrapper">
		<div class="nav">
			<a href="?v=home">返回</a>
		<?php foreach ($classCate as $key=>$value):?>
		<a href="?cate=<?php echo $key?>"><?php echo $value?></a>
		<?php endforeach?>
	</div>
		<div class="menu">

			<h2><?php echo $classCate[$cate]?></h2>
			<ul>
		<?php foreach ($classList[$cate] as $value):?>
		<li <?php if ($tag==$value):?> class='on' <?php endif?>><a
					href="?cate=<?php echo $cate?>&tag=<?php echo $value?>"><?php echo $value?></a></li>
		<?php endforeach?>
	</ul>
		</div>
		<div class="content">
			<h2><?php echo $doc['modifier']?> class <?php echo $doc['name']?></h2>
			<ul>
				<li><strong>说明</strong>
					<ul>
						<li><?php echo $doc['doc']?></li>
					</ul></li>
				<li><strong>位置</strong>
					<ul>
						<li><?php echo $doc['file']?> Line: <?php echo $doc['line']?></li>
					</ul></li>
				<li><strong>API</strong>

					<ul>
				<?php foreach ($doc['apiList'] as $key => $value):?>
				<li><a href="#fun_<?php echo $value['name']?>">
					<?php if ($value['isStatic']):?>
						<?php echo $doc['name']?>::<?php echo $value['name']?>()
					<?php else:?>
						<?php echo $doc['name']?>-><?php echo $value['name']?>()
					<?php
endif;					</a></li>
				<?php endforeach?>
			</ul></li>
				<li><strong>常量</strong>
					<ul>
			<?php if ($doc['const']):?>
				<?php foreach ($doc['const'] as $key => $value):?>
				<li><?php echo $key?> : <?php echo $value?></li>
				<?php endforeach?>
			<?php else:?>
				<li>无</li>
			<?php
endif;			</ul></li>
				<li><strong>属性</strong>
					<ul>
			<?php if ($doc['vars']):?>
				<?php foreach ($doc['vars'] as $key => $value):?>
				<li>
							<p class="code"><?php echo $value['modifier']?> $<?php echo $value['name']?></p>
							<p class="doc"><?php echo $value['doc']?></p>
						</li>
				<?php endforeach?>
			<?php else:?>
				<li>无</li>
			<?php
endif;			</ul></li>
				<li><strong>方法</strong>
					<ul>
			<?php foreach ($doc['method'] as $key => $value):?>
			<li id="fun_<?php echo $value['name']?>">
							<p class="code">
					<?php if ($value['isApi']):?>	
						<span class="api_span">[API]</span>
						<?php if ($value['isStatic']):?>
							<?php echo $doc['name']?>::<?php echo $value['name']?>(<?php echo $value['args']?>)
						<?php else:?>
							<?php echo $doc['name']?>-><?php echo $value['name']?>(<?php echo $value['args']?>)
						<?php
endif;					<?php else:?>
						<span class="method_span">[Method]</span>
						<?php echo $value['modifier']?> <?php echo $value['name']?>(<?php echo $value['args']?>)
					<?php endif?>
				</p>
							<p class="doc"><?php echo $value['doc']?></p>

						</li>
			<?php endforeach?>
			</ul></li>
			</ul>
		</div>
		<div style="clear: both"></div>
	</div>
</body>
</html>