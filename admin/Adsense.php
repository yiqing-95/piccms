<?php

/**
 * 广告管理
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-26 16:47
 * @version 1.0
 */
class Adsense_Controller extends Base_Controller {

    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $id = $this->input->getIntval('id');
        $list = load_model('Adsense')->getList();
        if ($id){
            $info = load_model('Adsense')->get($id);
            $this->output->set($info);
        }
        $this->output->set('list',$list);
        $this->output->set('id',$id);
        $this->output->display('adsense_show.html');
    }

    public function preview(){
        $id = $this->input->getIntval('id');
        $info = load_model('Adsense')->get($id);
        echo $info['content'];
    }

    public function add(){
        $id = $this->input->getIntval('id');
        if (check_submit('submit')){
            $title = $this->input->getTrim('title');
            $content = $this->input->getTrim('content');
            $des = $this->input->getTrim('des');
            if (!$title){
                show_msg("广告标识不能为空");
            }
            $modAdsense = load_model('Adsense');
            $list = $modAdsense->getList("title = '$title'");
            if ($list){
                if ($list[0]['id'] != $id){
                    show_msg("该标识已经存在");
                }
            }
            $modAdsense = load_model('Adsense');
            $data = array(
                    'title'=>$title,
                    'content'=>$content,
                    'des'=>$des
            );
            // 更新缓存
            load_model('Config')->clearFileCache();
            
            if ($id){
                $modAdsense->set($id,$data);
                show_msg("修改成功",'?c=Adsense');
            }else{
                $modAdsense->add($data);
                show_msg("添加成功",'?c=Adsense');
            }
        }
    }

    public function del(){
        $id = $this->input->getIntval('id');
        load_model('Adsense')->del($id);
        show_msg("删除成功",'?c=Adsense',0);
    }
}