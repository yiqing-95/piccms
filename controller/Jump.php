<?php

/**
 * 外部链接
 *
 * @author 小鱼哥哥
 *         @time 2011-12-7 15:56
 * @version 1.0
 */
class Jump_Controller extends Base_Controller {

    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $url = $this->input->get('url');
        $this->assignData();
        $this->output->set('url',$url);
        $this->output->display('jump.html');
    }

    public function top(){
        $this->assignData();
        $this->output->display('jump-top.html');
    }
}