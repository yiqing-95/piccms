<?php

/**
 * 主控制器
 *
 * @author YHS
 *         @time 2011-8-30 15:53
 * @version 1.0
 */
class Main_Controller extends Base_Controller {

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
        if (Wee::$config['html_cache_on']){
            $htmlFile = "index/index.html";
            $expire = Wee::$config['html_cache_index'] * 60;
            if ($this->cache->getFromHtml($htmlFile,$expire)){
                return;
            }
        }
        $this->_show();
        $this->output->display('index.html');
        if (Wee::$config['html_cache_on']){
            $this->cache->setToHtml($htmlFile,null,$expire);
        }
    }

    public function makeHtml(){
        if (!Wee::$config['url_html_index']){
            return;
        }
        $this->_show();
        $this->output->makeHtml('index.html',load_model('Web')->getIndexPath());
    }

    private function _show(){
        $this->assignData();
    }
}