<?php

/**
 * 管理员控制器
 * 
 * @author 小鱼哥哥
 *         @time 2011-11-16 17:35
 * @version 1.0
 */
class Admin_Controller extends Base_Controller {

    public function __construct(){
        parent::__construct();
        $this->checkLogin(Ext_Auth::SYS_EDIT);
    }

    /**
     * 管理员列表
     * 
     * @param
     *            mixed
     * @return void
     */
    public function show(){
        $uid = $this->input->getIntval('uid');
        $modAdmin = load_model('Admin');
        $dataList = $modAdmin->getAll();
        if ($uid){
            $this->output->set($dataList[$uid]);
        }
        if (check_submit()){
            $data['email'] = $this->input->getTrim('email');
            $data['pre'] = $this->input->getIntval('pre');
            $password = $this->input->getTrim('password');
            if ($password){
                if ($password != $this->input->getTrim('password2')){
                    show_msg('确认密码不一致');
                }
            }
            // 修改
            if ($uid){
                if ($password){
                    $data['password'] = Ext_String::passHash($password);
                }
                $modAdmin->set($uid,$data);
            }else{
                $data['name'] = $this->input->getTrim('name');
                if (!$data['name']){
                    show_msg('帐号不能为空');
                }
                $res = $modAdmin->getByname($data['name']);
                if ($res){
                    show_msg('帐号已经存在');
                }
                if (!$password){
                    show_msg('密码不能为空');
                }
                $data['password'] = Ext_String::passHash($password);
                $modAdmin->add($data);
            }
            show_msg('操作成功','?c=Admin&a=show');
        }
        $this->output->set('uid',$uid);
        $this->output->set('pres',Ext_Auth::$pres);
        $this->output->set('dataList',$dataList);
        $this->output->display('admin_show.html');
    }

    /**
     * 删除管理员
     * 
     * @param
     *            mixed
     * @return void
     */
    public function del(){
        $uid = $this->input->getIntval('uid');
        $modAdmin = load_model('Admin');
        $modAdmin->del($uid);
        show_msg('操作成功','?c=Admin&a=show',0);
    }
}