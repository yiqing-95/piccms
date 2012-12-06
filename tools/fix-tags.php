<html>
<title>PicCMS fix Tags</title>
<?php
// 应用程序路径
define('APP_PATH',
        rtrim(dirname(dirname(__FILE__)),'/\\') . DIRECTORY_SEPARATOR);

// 加载框加入口
require_once '../core/loader.php';

Wee::init();
$db = load_db();

$where = "1";
$modArticle = load_model('Article');
$start = 0;
$total = $modArticle->getTotal($where);
while($start < $total){
    $list = $modArticle->search($where,"$start, 100");
    foreach($list as $value){
        $tags = $modArticle->parseTags($value['title']);
        if ($tags){
            $modArticle->set($value['id'],array(
                    'tag'=>$tags
            ));
            $modArticle->setTags($value['id'],$tags,$value['title']);
            echo $value['id'] . ' ' . $value['title'] . ' ' . $tags . '<br>';
        }
    }
    $start += 100;
}
echo '重建标签成功';