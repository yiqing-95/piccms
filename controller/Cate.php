<?php

/**
 * 分类
 *
 * @author 小鱼哥哥
 *         @time 2011-12-7 15:56
 * @version 1.0
 */
class Cate_Controller extends Base_Controller {

    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $this->cid = $this->input->getIntval('cid');
        $this->page = $this->input->getIntval('p');
        if (Wee::$config['html_cache_on']){
            $htmlFile = "cate/{$this->cid}_{$this->page}.html";
            $expire = Wee::$config['html_cache_cate'] * 60;
            if ($this->cache->getFromHtml($htmlFile,$expire)){
                return;
            }
        }
        $this->_show();
        $this->output->display($this->template);
        if (Wee::$config['html_cache_on']){
            $this->cache->setToHtml($htmlFile,null,$expire);
        }
    }

    public function makeHtml(){
        if (!Wee::$config['url_html_cate']){
            return;
        }
        $this->cid = $this->input->getIntval('cid');
        $this->page = $this->input->getIntval('p');
        $this->_show();
        $this->output->makeHtml($this->template,
                load_model('Cate')->getPath($this->cate,$this->page));
    }

    private function _show(){
        $modCate = load_model('Cate');
        $this->cate = $modCate->getPlace($this->cid);
        if (!$this->cate){
            show_msg("分类不存在");
        }
        $modArticle = load_model('Article');
        if ($this->cate['sonId']){
            $where['cid'] = $this->cate['sonId'];
            array_unshift($where['cid'],$this->cid);
        }else{
            $where['cid'] = $this->cid;
        }
        $totalNum = $modArticle->getTotal($where);
        $url = array(
                $modCate,
                'getUrl',
                $this->cate
        );
        $pageInfo = new Ext_Page($url,$totalNum,$this->page,
                Wee::$config['web_list_pagenum']);
        $list = $modArticle->search($where,$pageInfo->limit());
        if ($this->cate['parent']){
            $this->output->set('cid',$this->cate['parent']['cid']);
        }else{
            $this->output->set('cid',$this->cid);
        }
        $title = $this->cate['name'];
        if ($this->cate['ctpl']){
            $this->template = $this->cate['ctpl'];
        }else{
            $this->template = 'cate.html';
        }
        $this->output->set('list',$list);
        $this->output->set('cate',$this->cate);
        $this->output->set('title',$title);
        $this->output->set('totalNum',$totalNum);
        $this->output->set('pageHtml',$pageInfo->html());
        $this->assignData();
    }
}