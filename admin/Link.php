<?php

/**
 * 友情链接
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-15 18:25
 * @version 1.0
 */
class Link_Controller extends Base_Controller {

    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $id = $this->input->getIntval('id');
        $linkList = load_model('Link')->getList();
        if ($id){
            $info = load_model('Link')->get($id);
            $this->output->set($info);
        }
        $this->output->set('linkList',$linkList);
        $this->output->display('link_show.html');
    }

    /**
     * 添加友情链接
     * 
     * @param
     *            mixed
     * @return void
     */
    public function add(){
        $id = $this->input->getIntval('id');
        // 检查是否为POST请求
        if (check_submit('submit')){
            $title = $this->input->getTrim('title');
            $url = $this->input->getTrim('url');
            $logo = $this->input->getTrim('logo');
            if (!$title){
                show_msg("网站名称不能为空");
            }
            $data = array(
                    'title'=>$title,
                    'url'=>$url,
                    'logo'=>$logo,
                    'oid'=>$this->input->getIntval('oid'),
                    'type'=>$this->input->getIntval('type')
            );
            
            load_model('Config')->clearFileCache();
            if ($id){
                load_model('Link')->set($id,$data);
                show_msg("修改成功",'?c=Link');
            }else{
                load_model('Link')->add($data);
                show_msg("添加成功",'?c=Link');
            }
        }
    }

    /**
     * 删除友情链接
     * 
     * @param
     *            mixed
     * @return void
     */
    public function del(){
        $id = $this->input->getIntval('id');
        load_model('Link')->del($id);
        show_msg("删除成功",'?c=Link',0);
    }
}