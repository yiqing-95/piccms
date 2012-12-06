<?php

/**
 * 配置管理
 * 
 * @author YHS
 *         @time 2011-8-30 15:53
 * @version 1.0
 */
class Config_Controller extends Base_Controller {

    public function __construct(){
        parent::__construct();
        $this->checkLogin(Ext_Auth::SYS_EDIT);
    }

    public function index(){
        $type = $this->input->get('type');
        if (!$type) $type = 'web';
        $modConfig = load_model('Config');
        if (check_submit()){
            $data = $this->input->get('con');
            $modConfig->setConfig($data);
            $modConfig->clearFileCache();
            show_msg("操作成功","?c=Config&type=$type");
        }
        if ('web' == $type){
            $skinList = $modConfig->getSkinList();
            $this->output->set('skinList',$skinList);
        }
        $this->output->set(Wee::$config);
        $this->output->display("config_$type.html");
    }

    /**
     * 清空文件缓存
     * 
     * @param
     *            mixed
     * @return void
     */
    public function clearCache(){
        $type = $this->input->get('type');
        $htmlCachePath = Wee::$config['data_path'] . 'html_cache/' .
                 Wee::$config['template_skin'] . '/';
        switch($type){
            case 'index':
                Ext_Dir::del($htmlCachePath . 'index/');
                break;
            case 'cate':
                Ext_Dir::del($htmlCachePath . 'cate/');
                break;
            case 'article':
                Ext_Dir::del($htmlCachePath . 'article/');
                break;
            case 'html':
                Ext_Dir::del(Wee::$config['data_path'] . 'html_cache/');
                break;
            case 'file':
                Ext_Dir::del(Wee::$config['data_path'] . 'cache/');
                break;
            case 'tpl':
                Ext_Dir::del(Wee::$config['data_path'] . 'tpl_compile/');
                break;
            default:
                Ext_Dir::del(Wee::$config['data_path'] . 'html_cache/');
                Ext_Dir::del(Wee::$config['data_path'] . 'cache/');
                Ext_Dir::del(Wee::$config['data_path'] . 'tpl_compile/');
        }
        show_msg('操作完成');
    }
}