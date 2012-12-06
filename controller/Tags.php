<?php

/**
 * 标签
 *
 * @author 小鱼哥哥
 *         @time 2011-12-7 15:56
 * @version 1.0
 */
class Tags_Controller extends Base_Controller {

    /**
     * initController
     *
     * @param
     *            mixed
     * @return void
     */
    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $this->assignData();
        $tag = Ext_Filter::sqlChars($this->input->getTrim('tag'));
        $p = $this->input->getIntval('p');
        if (!$tag){
            show_msg('标签不能为空');
        }
        $modArticle = load_model('Article');
        $totalNum = $modArticle->getTagsTotal($tag);
        $url = url('Tags','',
                array(
                        'tag'=>$tag,
                        'p'=>'@'
                ));
        $pageInfo = new Ext_Page($url,$totalNum,$p,
                Wee::$config['web_list_pagenum']);
        $list = $modArticle->getTagsArticle($tag,$pageInfo->limit());
        
        $title = $tag;
        $this->output->set('title',$title);
        $this->output->set('list',$list);
        $this->output->set('tag',$tag);
        $this->output->set('totalNum',$totalNum);
        $this->output->set('pageHtml',$pageInfo->html());
        $this->output->display('tags.html');
    }
}