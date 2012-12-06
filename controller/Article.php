<?php

/**
 * 文章
 *
 * @author 小鱼哥哥
 *         @time 2011-12-7 15:56
 * @version 1.0
 */
class Article_Controller extends Base_Controller {

    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $this->id = $this->input->getIntval('id');
        $this->page = $this->input->getIntval('p');
        if (Wee::$config['html_cache_on']){
            $htmlFile = "article/" . floor($this->id / 100) .
                     "/{$this->id}_{$this->page}.html";
            $expire = Wee::$config['html_cache_content'] * 60;
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
        if (!Wee::$config['url_html_content']){
            return;
        }
        $this->id = $this->input->getIntval('id');
        $this->page = $this->input->getIntval('p');
        $this->_show();
        $this->output->makeHtml($this->template,
                load_model('Article')->getPath($this->id,$this->page));
    }

    public function _show(){
        if ($this->page < 1){
            $this->page = 1;
        }
        $modArticle = load_model('Article');
        $modAttach = load_model('Attach');
        $modCate = load_model('Cate');
        $article = $modArticle->get($this->id);
        if (!$article){
            show_msg('文章不存在');
        }
        $this->cate = $modCate->getPlace($article['cid']);
        if (1 == $this->cate['view_type']){
            Wee::$config['web_article_pagenum'] = 1;
            $article['thumb'] = $modAttach->getThumbList($this->id,null);
            $this->template = 'slide.html';
        }else{
            $this->template = 'article.html';
        }
        $article['pre'] = $modArticle->getPre($this->id);
        $article['next'] = $modArticle->getNext($this->id);
        $url = array(
                $modArticle,
                'getUrl',
                $this->id
        );
        $article['attach_num'] = $modAttach->getAttachNum($this->id);
        $pageInfo = new Ext_Page($url,$article['attach_num'],$this->page,
                Wee::$config['web_article_pagenum'],10);
        $article['total_page'] = $pageInfo->totalPage();
        $article['attach'] = $modAttach->getAttachList($this->id,
                $pageInfo->limit());
        // 点击图片跳到下一张
        if ($this->page < $article['total_page']){
            $article['next_url'] = $modArticle->getUrl($this->id,
                    $this->page + 1);
        }elseif ($article['next']){
            $article['next_url'] = $article['next']['url'];
        }else{
            $article['next_url'] = Wee::$config['web_url'];
        }
        $title = $article['title'];
        $this->output->set('pageHtml',$pageInfo->html());
        $this->output->set('page',$this->page);
        $this->output->set('title',$title);
        $this->output->set('article',$article);
        $this->output->set('cate',$this->cate);
        if ($this->cate['parent']){
            $this->output->set('cid',$this->cate['parent']['cid']);
        }else{
            $this->output->set('cid',$article['cid']);
        }
        $this->output->set('web_comment',Wee::$config['web_comment']);
        $this->assignData();
    }

    public function loadInfo(){
        $this->output->setDataMode(true);
        $id = $this->input->getIntval('id');
        $type = $this->input->get('type');
        
        if ('up' == $type || 'down' == $type){
            if (!Cookie::get("p_c_a_up_$id")){
                load_model('Article')->set($id,"$type = $type + 1");
                Cookie::set("p_c_a_up_$id",'1',3600);
            }else{
                show_msg('亲, 您已经顶过了~');
            }
        }else{
            if (!Cookie::get("p_c_a_hist_$id")){
                load_model('Article')->set($id,"hits = hits + 1");
                Cookie::set("p_c_a_hist_$id",'1',3600);
            }
        }
        $article = load_model('Article')->get($id,false);
        
        $this->output->set('hits',$article['hits']);
        $this->output->set('up',$article['up']);
        $this->output->set('down',$article['down']);
    }
}