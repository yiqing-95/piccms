<?php

/**
 * 归档
 *
 * @author 小鱼哥哥
 *         @time 2011-12-7 15:56
 * @version 1.0
 */
class Archiver_Controller extends Base_Controller {

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
        $this->page = $this->input->getIntval('p');
        $modArticle = load_model('Article');
        $where = array();
        $totalNum = $modArticle->getTotal($where);
        $url = url('Archiver','',array(
                'p'=>'@'
        ));
        $pageInfo = new Ext_Page($url,$totalNum,$this->page,100);
        $list = $modArticle->search($where,$pageInfo->limit());
        $this->output->set('list',$list);
        $this->output->set('pageHtml',$pageInfo->html());
        $this->assignData();
        $this->output->display('archiver.html');
    }
}