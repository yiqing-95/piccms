<?php

/**
 * 插件管理
 * 
 * @author YHS
 *         @time 2011-8-30 15:53
 * @version 1.0
 */
class Hack_Controller extends Base_Controller {

    public function __construct(){
        parent::__construct();
        $this->checkLogin(Ext_Auth::SYS_EDIT);
    }

    public function index(){
        $m = $this->input->get('m');
        $do = $this->input->get('do');
        if (!$do){
            $do = 'index';
        }
        Ext_Hack::handle($m,$do);
    }

    public function show(){
        if (check_submit()){
            $openArr = $this->input->get('open');
            $openHack = array();
            foreach($openArr as $key=>$value){
                if ($value){
                    $openHack[] = $key;
                }
            }
            load_model('Config')->setConfig('open_hack',implode('|',$openHack));
            show_msg('操作成功','?c=Hack&a=show');
        }
        $hList = Ext_Hack::gethackList();
        foreach($hList as & $value){
            if (in_array($value['key'],Wee::$config['open_hack'])){
                $value['open'] = 1;
            }else{
                $value['open'] = 0;
            }
        }
        unset($value);
        $this->output->set('hList',$hList);
        $this->output->display('hack_show.html');
    }
}