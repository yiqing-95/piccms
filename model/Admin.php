<?php

/**
 * 管理员
 *
 * @author 小鱼哥哥
 *         @time 2011-9-6 15:33
 * @version 1.0
 */
class Admin_Model extends Model {

    private $_adminInfo = null;

    public function __construct(){
        parent::__construct();
    }

    public function getAll(){
        $rs = $this->db->table('#@_admin')->getAll('uid');
        return $rs;
    }

    public function getByname($name){
        $rs = $this->db->table('#@_admin')
            ->where("name = '$name'")
            ->getOne();
        return $rs;
    }

    public function getByUid($uid){
        $rs = $this->db->table('#@_admin')
            ->where("uid = $uid")
            ->getOne();
        return $rs;
    }

    public function add($data){
        $this->db->table('#@_admin')->insert($data);
        return true;
    }

    public function set($uid, $data){
        $this->db->table('#@_admin')
            ->where("uid = $uid")
            ->update($data);
        return true;
    }

    public function del($uid){
        $this->db->table('#@_admin')
            ->where("uid = $uid")
            ->delete();
        return true;
    }

    public function login($name, $password){
        $password = Ext_String::passHash($password);
        $rs = $this->db->table('#@_admin')
            ->where("name = '$name' AND password = '$password'")
            ->getOne();
        if ($rs){
            $this->_adminInfo = $rs;
            $data = array(
                    'uid'=>$rs['uid'],
                    'name'=>$rs['name'],
                    'password'=>$rs['password']
            );
            Cookie::set('U_admin',$data);
        }
        return $rs;
    }

    public function getAdminInfo(){
        if ($this->_adminInfo){
            return $this->_adminInfo;
        }
        $cData = Cookie::get('U_admin');
        if ($cData){
            $name = $cData['name'];
            $password = $cData['password'];
            $rs = $this->db->table('#@_admin')
                ->where("name = '$name' AND password = '$password'")
                ->getOne();
            if ($rs){
                $this->_adminInfo = $rs;
                return $this->_adminInfo;
            }
        }
        return false;
    }

    public function logout(){
        $this->_adminInfo = null;
        Cookie::delete('U_admin');
    }
}