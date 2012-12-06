<?php

/**
 * 评论管理
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-26 15:04
 * @version 1.0
 */
class Comment_Controller extends Base_Controller {

    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $p = $this->input->getIntval('p');
        $articleId = $this->input->getIntval('article_id');
        $keyword = $this->input->get('keyword');
        $order = $this->input->get('order');
        $by = $this->input->get('by');
        $status = $this->input->get('status');
        $modComment = load_model('Comment');
        $where = array();
        if ($articleId){
            $where['article_id'] = $articleId;
        }
        if ($keyword){
            $where[] = "content LIKE '%{$keyword}%'";
        }
        if (Ext_Valid::check($status,'number')){
            $where['status'] = $status;
        }
        if (!$order){
            $order = 'id';
        }
        if (!$by){
            $by = 'DESC';
        }
        $total = $modComment->getTotal($where);
        $url = "javascript:showpage('@')";
        $pageInfo = new Ext_Page($url,$total,$p,
                Wee::$config['web_admin_pagenum']);
        $list = $modComment->getAll($where,$pageInfo->limit(),$order,$by);
        $this->output->set('list',$list);
        $this->output->set('pageHtml',$pageInfo->html());
        $this->output->set(
                array(
                        'p'=>$p,
                        'article_id'=>$articleId,
                        'keyword'=>$keyword,
                        'order'=>$order,
                        'by'=>$by,
                        'status'=>$status
                ));
        $this->output->display('comment_show.html');
    }

    public function setStatus(){
        $id = $this->input->get('id');
        $status = $this->input->getIntval('status');
        if (is_array($id)){
            foreach($id as $value){
                load_model('Comment')->set($value,array(
                        'status'=>$status
                ));
            }
        }else{
            load_model('Comment')->set($id,array(
                    'status'=>$status
            ));
        }
    }

    public function del(){
        $id = $this->input->get('id');
        if (is_array($id)){
            foreach($id as $value){
                load_model('Comment')->del($id);
            }
        }else{
            load_model('Comment')->del($id);
        }
    }
}