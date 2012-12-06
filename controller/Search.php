<?php

/**
 * 搜索
 *
 * @author 小鱼哥哥
 *         @time 2011-12-7 15:56
 * @version 1.0
 */
class Search_Controller extends Base_Controller {

    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $this->assignData();
        $keyword = Ext_Filter::sqlChars($this->input->getTrim('keyword'));
        $p = $this->input->getIntval('p');
        $modArticle = load_model('Article');
        if ($keyword){
            $where[] = "(tag LIKE '%$keyword%' OR title LIKE '%$keyword%')";
        }else{
            show_msg('关键词不能为空');
        }
        $totalNum = $modArticle->getTotal($where);
        $url = url('Search','',
                array(
                        'keyword'=>$keyword,
                        'p'=>'@'
                ));
        $pageInfo = new Ext_Page($url,$totalNum,$p,
                Wee::$config['web_list_pagenum']);
        $list = $modArticle->search($where,$pageInfo->limit());
        
        $title = $keyword;
        $this->output->set('title',$title);
        $this->output->set('list',$list);
        $this->output->set('keyword',$keyword);
        $this->output->set('totalNum',$totalNum);
        $this->output->set('pageHtml',$pageInfo->html());
        $this->output->display('search.html');
    }
}