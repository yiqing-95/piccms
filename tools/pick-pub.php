<?php
/**
 * 火车头采集发布接口
 * 
 * @author 小鱼哥哥
 *         @time 2012-1-13 17:00
 * @version 1.0
 */

// 应用程序路径
define('APP_PATH',
        rtrim(dirname(dirname(__FILE__)),'/\\') . DIRECTORY_SEPARATOR);

// 加载框加入口
require_once '../core/loader.php';

// 采集完成, 请勿必修改此发布接口文件名或者删除改文件
class Pub_Controller extends Controller {

    public function __construct(){
        parent::__construct();
    }

    public function add(){
        $cateList = load_model('Cate')->getList();
        $modArticle = load_model('Article');
        $data = array(
                'cid'=>$this->input->getIntval('cid'),
                'star'=>1,
                'status'=>$this->input->getIntval('status'),
                'title'=>$this->input->getTrim('title'),
                'tag'=>$this->input->getTrim('tag'),
                'author'=>$this->input->getTrim('author'),
                'hits'=>mt_rand(0,Wee::$config['web_pick_hits']),
                'up'=>mt_rand(0,Wee::$config['web_pick_up']),
                'addtime'=>$this->input->getTrim('addtime'),
                'remark'=>$this->input->getTrim('remark'),
                'content'=>$this->input->getTrim('content')
        );
        if (!$data['cid']){
            exit("error:请选择分类");
        }
        if (!isset($cateList[$data['cid']])){
            exit("error:分类不存在");
        }
        if (!$data['title']){
            exit("error:标题不能为空");
        }
        if (!$data['content']){
            exit("error:内容不能为空");
        }
        if (!$data['tag']){
            $data['tag'] = $modArticle->parseTags($data['title']);
        }
        if ($data['addtime']){
            $data['addtime'] = strtotime($data['addtime']);
        }else{
            $data['addtime'] = mt_rand(time() - 3600 * 24 * 30,time());
        }
        $content = stripslashes($data['content']);
        $data['content'] = strip_tags($data['content']);
        $articleId = $modArticle->add($data);
        if ($data['tag']){
            $modArticle->setTags($articleId,$data['tag'],$data['title']);
        }
        if ($content){
            $patten = '/<img.*?src=[\'\"]?([^\s>\"\']+)[\'\"][^>]*>([^<]*)/is';
            if (preg_match_all($patten,$content,$arr)){
                if (!empty($arr[1])){
                    $dataAttach = array();
                    $modAttach = load_model('Attach');
                    foreach($arr[1] as $key=>$value){
                        $value = trim($value,"\\/");
                        $ext = $modAttach->getExt($value);
                        $dataAttach[] = array(
                                'article_id'=>$articleId,
                                'name'=>basename($value),
                                'remark'=>trim($arr[2][$key]),
                                'file'=>$value,
                                'ext'=>$ext,
                                'size'=>0,
                                'upload_time'=>Ext_Date::now(),
                                'type'=>$modAttach->isHttp($value)
                        );
                    }
                    $modAttach->add($dataAttach);
                    if (isset($dataAttach[0])){
                        $modArticle->set($articleId,
                                array(
                                        'cover'=>$dataAttach[0]['file']
                                ));
                    }
                }
            }
        }
        echo 'add success!';
    }
}

Wee::run('Pub','add');