<?php

/**
 * web主页
 * 
 * @author 小鱼哥哥
 *         @time 2011-12-27 17:31
 * @version 1.0
 */
class Web_Model extends Model {

    public function __construct(){
        parent::__construct();
    }

    /**
     * 获取首页路径
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getIndexPath($page = 0){
        return APP_PATH . $this->_getName($page);
    }

    /**
     * 获取首页访问地址
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getIndexUrl($page = 0){
        if (Wee::$config['url_html_index']){
            $url = Wee::$config['web_url'] . $this->_getName($page);
        }else{
            $url = url('','',array(
                    'p'=>$page
            ));
        }
        return $url;
    }

    private function _getName($page = 0){
        $name = "index";
        if ($page > 1){
            $name .= "_$page";
        }
        return $name . Wee::$config['url_suffix'];
    }

    public function getIndex($p = 0){}
}