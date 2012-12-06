<?php

/**
 * 权限管理
 * 
 * @author 小鱼哥哥
 *         @time 2011-11-17 10:59
 * @version 1.0
 */
class Ext_Auth {

    /**
     *
     * @var mixed 内容编辑
     */
    const CONTENT_EDIT = 1;

    /**
     *
     * @var mixed 栏目编辑
     */
    const CATE_EDIT = 2;

    /**
     *
     * @var mixed 网站编辑
     */
    const WEB_EDIT = 4;

    /**
     *
     * @var mixed 系统配置
     */
    const SYS_EDIT = 8;

    /**
     *
     * @var mixed 权限对应表
     */
    public static $pres = array(
            '1'=>'内容编辑',
            '3'=>'栏目编辑',
            '7'=>'整站编辑',
            '15'=>'管理员'
    );

    /**
     * 返回权限组合
     * 
     * @param
     *            mixed
     * @return void
     */
    public function getPre($arg){
        $args = func_get_args();
        $pre = 0;
        foreach($args as $value){
            $pre |= $value;
        }
        return $pre;
    }

    /**
     * 检查权限
     * 
     * @param
     *            mixed
     * @return void
     */
    public function check($havePre, $needPre){
        return $havePre & $needPre;
    }
}